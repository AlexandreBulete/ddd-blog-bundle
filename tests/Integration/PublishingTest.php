<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Tests\Integration;

use AlexandreBulete\DddBlogBundle\Application\Command\CategorizePost\CategorizePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\CategorizePost\CategorizePostHandler;
use AlexandreBulete\DddBlogBundle\Application\Command\CreateCategory\CreateCategoryCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\CreateCategory\CreateCategoryHandler;
use AlexandreBulete\DddBlogBundle\Application\Command\DraftPost\DraftPostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\DraftPost\DraftPostHandler;
use AlexandreBulete\DddBlogBundle\Application\Command\PublishPost\PublishPostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\PublishPost\PublishPostHandler;
use AlexandreBulete\DddBlogBundle\Application\Command\RemoveCategory\RemoveCategoryCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\RemoveCategory\RemoveCategoryHandler;
use AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPost\FindPublishedPostHandler;
use AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPost\FindPublishedPostQuery;
use AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPosts\FindPublishedPostsHandler;
use AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPosts\FindPublishedPostsQuery;
use AlexandreBulete\DddBlogBundle\Application\Service\CategoryGuard;
use AlexandreBulete\DddBlogBundle\Application\Service\TagResolver;
use AlexandreBulete\DddBlogBundle\Domain\Exception\NotFoundBySlugException;
use AlexandreBulete\DddBlogBundle\Domain\Exception\SlugAlreadyUsedException;
use AlexandreBulete\DddBlogBundle\Domain\Exception\StillInUseException;
use AlexandreBulete\DddBlogBundle\Domain\Exception\UnknownCategoryException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\DoctrineCategoryRepository;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\DoctrinePostRepository;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\DoctrineTagRepository;
use AlexandreBulete\DddBlogBundle\Infrastructure\Text\AsciiSlugGenerator;
use AlexandreBulete\DddBlogBundle\Tests\Integration\Fixture\NullEventPublisher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * The use cases against a real database: what the public sees, when.
 */
final class PublishingTest extends TestCase
{
    private EntityManagerInterface $em;
    private MockClock $clock;
    private DoctrinePostRepository $posts;
    private DoctrineCategoryRepository $categories;
    private DoctrineTagRepository $tags;

    protected function setUp(): void
    {
        $this->em = BlogDatabase::migrated();
        $this->clock = new MockClock('2026-11-20 10:00:00');
        $publisher = new NullEventPublisher();
        $this->posts = new DoctrinePostRepository($this->em, $publisher);
        $this->categories = new DoctrineCategoryRepository($this->em, $publisher);
        $this->tags = new DoctrineTagRepository($this->em, $publisher);
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->close();
    }

    #[Test]
    public function a_scheduled_post_appears_when_its_date_passes(): void
    {
        $post = $this->draft('Bientôt');
        $this->publish($post, $this->clock->now()->modify('+1 day'));

        self::assertCount(0, $this->published(new FindPublishedPostsQuery()));
        $this->assertNotFound('bientot');

        $this->clock->modify('+1 day');
        $this->em->clear();

        self::assertCount(1, $this->published(new FindPublishedPostsQuery()));
        self::assertSame('Bientôt', $this->findPublished('bientot')->title);
    }

    #[Test]
    public function a_post_is_listed_under_each_of_its_categories(): void
    {
        $novels = $this->category('Romans');
        $essays = $this->category('Essais');
        $both = $this->draft('Les deux', [$novels->id, $essays->id]);
        $one = $this->draft('Un seul', [$novels->id]);
        $this->publish($both);
        $this->publish($one);
        $this->em->clear();

        self::assertSame(['Un seul', 'Les deux'], $this->titles(new FindPublishedPostsQuery(categoryId: $novels->id)));
        self::assertSame(['Les deux'], $this->titles(new FindPublishedPostsQuery(categoryId: $essays->id)));
        self::assertSame(2, $this->posts->countFiledUnder($novels->id));
    }

    #[Test]
    public function recategorizing_moves_the_post_between_listings(): void
    {
        $novels = $this->category('Romans');
        $essays = $this->category('Essais');
        $post = $this->draft('Mobile', [$novels->id]);
        $this->publish($post);

        (new CategorizePostHandler($this->posts, new CategoryGuard($this->categories), $this->clock))(new CategorizePostCommand($post->id, [$essays->id]));
        $this->em->clear();

        self::assertSame([], $this->titles(new FindPublishedPostsQuery(categoryId: $novels->id)));
        self::assertSame(['Mobile'], $this->titles(new FindPublishedPostsQuery(categoryId: $essays->id)));
    }

    #[Test]
    public function tags_are_created_once_whatever_their_case(): void
    {
        $first = $this->draft('Premier', tags: ['Polar', 'Été']);
        $second = $this->draft('Second', tags: ['polar ']);
        $this->publish($first);
        $this->publish($second);
        $this->em->clear();

        self::assertCount(2, $this->tags->withoutPagination());
        $polar = $this->tags->findBySlug(Slug::fromString('polar'));
        self::assertNotNull($polar);
        self::assertSame('Polar', $polar->name);
        self::assertSame(['Second', 'Premier'], $this->titles(new FindPublishedPostsQuery(tagId: $polar->id)));
    }

    #[Test]
    public function a_slug_is_unique(): void
    {
        $this->draft('Même titre');

        $this->expectException(SlugAlreadyUsedException::class);
        $this->draft('Même titre !');
    }

    #[Test]
    public function a_post_cannot_be_filed_under_an_unknown_category(): void
    {
        $this->expectException(UnknownCategoryException::class);
        $this->draft('Perdu', [CategoryId::generate()]);
    }

    #[Test]
    public function a_category_still_in_use_cannot_be_removed(): void
    {
        $novels = $this->category('Romans');
        $this->draft('Dedans', [$novels->id]);

        $this->expectException(StillInUseException::class);
        (new RemoveCategoryHandler($this->categories, $this->posts))(new RemoveCategoryCommand($novels->id));
    }

    #[Test]
    public function related_posts_share_the_most_categories_and_tags(): void
    {
        $novels = $this->category('Romans');
        $essays = $this->category('Essais');
        $post = $this->draft('Référence', [$novels->id, $essays->id], ['polar']);
        $close = $this->draft('Proche', [$novels->id, $essays->id], ['polar']);
        $far = $this->draft('Lointain', [$novels->id]);
        $unrelated = $this->draft('Sans rapport');
        $draft = $this->draft('Brouillon', [$novels->id, $essays->id], ['polar']);
        foreach ([$post, $close, $far, $unrelated] as $p) {
            $this->publish($p);
        }
        unset($draft);
        $this->em->clear();

        $reference = $this->posts->findBySlug(Slug::fromString('reference'));
        self::assertNotNull($reference);
        $related = $this->posts->relatedTo($reference, $this->clock->now(), 3);

        self::assertSame(['Proche', 'Lointain', 'Sans rapport'], array_map(static fn (Post $p): string => $p->title, $related));
    }

    #[Test]
    public function a_post_listing_paginates(): void
    {
        foreach (range(1, 5) as $i) {
            $post = $this->draft("Article {$i}");
            $this->clock->modify('+1 minute');
            $this->publish($post);
        }

        $page = $this->published(new FindPublishedPostsQuery(page: 2, itemsPerPage: 2));

        self::assertSame(5, $page->count());
        self::assertSame(['Article 3', 'Article 2'], array_map(static fn (Post $p): string => $p->title, iterator_to_array($page, false)));
    }

    /**
     * @param list<CategoryId> $categoryIds
     * @param list<string>     $tags
     */
    private function draft(string $title, array $categoryIds = [], array $tags = []): Post
    {
        $slugger = new AsciiSlugGenerator();

        return (new DraftPostHandler($this->posts, $slugger, new CategoryGuard($this->categories), new TagResolver($this->tags, $slugger), $this->clock))(
            new DraftPostCommand(title: $title, body: 'Quelques mots.', categoryIds: $categoryIds, tags: $tags),
        );
    }

    private function publish(Post $post, ?\DateTimeImmutable $at = null): void
    {
        (new PublishPostHandler($this->posts, $this->clock))(new PublishPostCommand($post->id, $at));
    }

    private function category(string $name): Category
    {
        return (new CreateCategoryHandler($this->categories, new AsciiSlugGenerator(), $this->clock))(new CreateCategoryCommand($name));
    }

    private function published(FindPublishedPostsQuery $query): DoctrinePostRepository
    {
        $result = (new FindPublishedPostsHandler($this->posts, $this->clock))($query);
        self::assertInstanceOf(DoctrinePostRepository::class, $result);

        return $result;
    }

    /**
     * @return list<string>
     */
    private function titles(FindPublishedPostsQuery $query): array
    {
        return array_map(static fn (Post $p): string => $p->title, iterator_to_array($this->published($query), false));
    }

    private function findPublished(string $slug): Post
    {
        return (new FindPublishedPostHandler($this->posts, $this->clock))(new FindPublishedPostQuery(Slug::fromString($slug)));
    }

    private function assertNotFound(string $slug): void
    {
        try {
            $this->findPublished($slug);
            self::fail("{$slug} should not be visible");
        } catch (NotFoundBySlugException) {
            $this->addToAssertionCount(1);
        }
    }
}
