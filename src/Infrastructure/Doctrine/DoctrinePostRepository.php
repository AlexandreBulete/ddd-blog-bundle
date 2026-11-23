<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Model\PostStatus;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\VisualId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index\PostCategoryLink;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index\PostTagLink;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\CategoryIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\PostIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\SlugType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\TagIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\VisualIdType;
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Posts, plus the two read indexes of their categories and tags.
 *
 * The indexes are written here with DBAL, in the transaction of the post
 * itself: they are a projection of the post row, never edited on their own,
 * and going through the ORM would keep their rows in the identity map — the
 * next save of the same post would collide with them.
 *
 * Filters on categories and tags are EXISTS subqueries, not joins: the bridge
 * paginates without DISTINCT, which a to-many join would break.
 *
 * @extends DoctrineRepository<Post>
 */
final class DoctrinePostRepository extends DoctrineRepository implements PostRepositoryInterface
{
    private const string ALIAS = 'post';
    private const string CATEGORY_INDEX = 'blog_post_category';
    private const string TAG_INDEX = 'blog_post_tag';

    public function __construct(
        EntityManagerInterface $em,
        private readonly DomainEventPublisherInterface $eventPublisher,
    ) {
        parent::__construct($em, Post::class, self::ALIAS);
    }

    public function save(Post $post): void
    {
        $this->em->wrapInTransaction(function () use ($post): void {
            $this->em->persist($post);
            $this->em->flush();
            $this->reindex($post->id, $post->categoryIds, $post->tagIds);
            $this->eventPublisher->publishAll($post->releaseEvents());
        });
    }

    public function remove(Post $post): void
    {
        $this->em->wrapInTransaction(function () use ($post): void {
            $this->reindex($post->id, [], []);
            $this->em->remove($post);
            $this->em->flush();
            $this->eventPublisher->publishAll($post->releaseEvents());
        });
    }

    public function findById(IdentifierVO $id): ?Post
    {
        return $this->em->find(Post::class, $id->value());
    }

    public function findBySlug(Slug $slug): ?Post
    {
        $post = $this->query()
            ->andWhere(self::ALIAS . '.slug = :slug')
            ->setParameter('slug', $slug, SlugType::NAME)
            ->getQuery()
            ->getOneOrNullResult();

        return $post instanceof Post ? $post : null;
    }

    public function visibleAt(\DateTimeImmutable $now): static
    {
        return $this->constrained(static function (QueryBuilder $qb, string $alias) use ($now): void {
            self::whereVisible($qb, $alias, $now);
            $qb->addOrderBy("{$alias}.publishedAt", 'DESC')
                ->addOrderBy("{$alias}.id", 'DESC');
        });
    }

    public function filedUnder(CategoryId $categoryId): static
    {
        return $this->constrained(static function (QueryBuilder $qb, string $alias) use ($categoryId): void {
            $qb->andWhere(sprintf(
                'EXISTS (SELECT 1 FROM %s pc WHERE pc.postId = %s.id AND pc.categoryId = :filedUnder)',
                PostCategoryLink::class,
                $alias,
            ))->setParameter('filedUnder', $categoryId, CategoryIdType::NAME);
        });
    }

    public function taggedWith(TagId $tagId): static
    {
        return $this->constrained(static function (QueryBuilder $qb, string $alias) use ($tagId): void {
            $qb->andWhere(sprintf(
                'EXISTS (SELECT 1 FROM %s pt WHERE pt.postId = %s.id AND pt.tagId = :taggedWith)',
                PostTagLink::class,
                $alias,
            ))->setParameter('taggedWith', $tagId, TagIdType::NAME);
        });
    }

    public function featuredOnly(): static
    {
        return $this->constrained(static function (QueryBuilder $qb, string $alias): void {
            $qb->andWhere("{$alias}.featured = :featured")->setParameter('featured', true, Types::BOOLEAN);
        });
    }

    public function countFiledUnder(CategoryId $categoryId): int
    {
        return $this->countIndex(PostCategoryLink::class, 'categoryId', $categoryId, CategoryIdType::NAME);
    }

    public function countTaggedWith(TagId $tagId): int
    {
        return $this->countIndex(PostTagLink::class, 'tagId', $tagId, TagIdType::NAME);
    }

    public function withVisual(VisualId $visual): array
    {
        /** @var list<Post> $posts */
        $posts = $this->em->createQueryBuilder()
            ->select(self::ALIAS)
            ->from(Post::class, self::ALIAS)
            ->where(self::ALIAS . '.visual = :visual')
            ->setParameter('visual', $visual, VisualIdType::NAME)
            ->orderBy(self::ALIAS . '.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $posts;
    }

    public function countVisibleByCategory(\DateTimeImmutable $now): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('l.categoryId AS categoryId', 'COUNT(l.postId) AS posts')
            ->from(PostCategoryLink::class, 'l')
            ->from(Post::class, 'p')
            ->where('p.id = l.postId')
            ->groupBy('l.categoryId');
        self::whereVisible($qb, 'p', $now);

        /** @var list<array{categoryId: CategoryId, posts: int|string}> $rows */
        $rows = $qb->getQuery()->getResult();
        $counts = [];
        foreach ($rows as $row) {
            $counts[(string) $row['categoryId']] = (int) $row['posts'];
        }

        return $counts;
    }

    /**
     * Scored in PHP from the two indexes (one point per shared category or
     * tag), then the candidates loaded at once; topped up with the most recent
     * posts when too few share anything. Two small queries over indexed
     * columns, where a single portable SQL query would need a UNION DQL lacks.
     */
    public function relatedTo(Post $post, \DateTimeImmutable $now, int $limit): array
    {
        $scores = [];
        foreach ([
            [PostCategoryLink::class, 'categoryId', $post->categoryIds, CategoryIdType::NAME],
            [PostTagLink::class, 'tagId', $post->tagIds, TagIdType::NAME],
        ] as [$index, $field, $ids, $type]) {
            if ($ids === []) {
                continue;
            }
            /** @var list<array{postId: PostId, shared: int|string}> $rows */
            $rows = $this->em->createQueryBuilder()
                ->select('l.postId AS postId', 'COUNT(l.postId) AS shared')
                ->from($index, 'l')
                ->where("l.{$field} IN (:ids)")
                ->andWhere('l.postId <> :self')
                ->groupBy('l.postId')
                ->setParameter('ids', array_map(static fn (IdentifierVO $id): string => $id->toRfc4122(), $ids))
                ->setParameter('self', $post->id, PostIdType::NAME)
                ->getQuery()
                ->getResult();
            foreach ($rows as $row) {
                $key = (string) $row['postId'];
                $scores[$key] = ($scores[$key] ?? 0) + (int) $row['shared'];
            }
        }

        $related = [];
        if ($scores !== []) {
            $qb = $this->query();
            self::whereVisible($qb, self::ALIAS, $now);
            /** @var list<Post> $candidates */
            $candidates = $qb->andWhere(self::ALIAS . '.id IN (:candidates)')
                ->setParameter('candidates', array_keys($scores))
                ->getQuery()
                ->getResult();
            usort($candidates, static fn (Post $a, Post $b): int => [$scores[(string) $b->id], $b->publishedAt] <=> [$scores[(string) $a->id], $a->publishedAt]);
            $related = array_slice($candidates, 0, $limit);
        }

        if (count($related) < $limit) {
            $qb = $this->query();
            self::whereVisible($qb, self::ALIAS, $now);
            $exclude = [$post->id->toRfc4122(), ...array_map(static fn (Post $p): string => $p->id->toRfc4122(), $related)];
            /** @var list<Post> $recent */
            $recent = $qb->andWhere(self::ALIAS . '.id NOT IN (:exclude)')
                ->setParameter('exclude', $exclude)
                ->orderBy(self::ALIAS . '.publishedAt', 'DESC')
                ->setMaxResults($limit - count($related))
                ->getQuery()
                ->getResult();
            $related = [...$related, ...$recent];
        }

        return $related;
    }

    private static function whereVisible(QueryBuilder $qb, string $alias, \DateTimeImmutable $now): void
    {
        $qb->andWhere("{$alias}.status = :visibleStatus")
            ->andWhere("{$alias}.publishedAt <= :visibleAt")
            ->setParameter('visibleStatus', PostStatus::Published->value)
            ->setParameter('visibleAt', $now, Types::DATETIME_IMMUTABLE);
    }

    /**
     * @param class-string $index
     */
    private function countIndex(string $index, string $field, IdentifierVO $id, string $type): int
    {
        $count = $this->em->createQueryBuilder()
            ->select('COUNT(l.postId)')
            ->from($index, 'l')
            ->where("l.{$field} = :id")
            ->setParameter('id', $id, $type)
            ->getQuery()
            ->getSingleScalarResult();

        return is_numeric($count) ? (int) $count : 0;
    }

    /**
     * @param list<CategoryId> $categoryIds
     * @param list<TagId>      $tagIds
     */
    private function reindex(PostId $postId, array $categoryIds, array $tagIds): void
    {
        $connection = $this->em->getConnection();
        $post = $postId->toRfc4122();

        $connection->delete(self::CATEGORY_INDEX, ['post_id' => $post]);
        foreach ($categoryIds as $categoryId) {
            $connection->insert(self::CATEGORY_INDEX, ['post_id' => $post, 'category_id' => $categoryId->toRfc4122()]);
        }

        $connection->delete(self::TAG_INDEX, ['post_id' => $post]);
        foreach ($tagIds as $tagId) {
            $connection->insert(self::TAG_INDEX, ['post_id' => $post, 'tag_id' => $tagId->toRfc4122()]);
        }
    }
}
