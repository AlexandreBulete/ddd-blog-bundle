<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\SlugType;
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<Tag>
 */
final class DoctrineTagRepository extends DoctrineRepository implements TagRepositoryInterface
{
    private const string ALIAS = 'tag';

    public function __construct(
        EntityManagerInterface $em,
        private readonly DomainEventPublisherInterface $eventPublisher,
    ) {
        parent::__construct($em, Tag::class, self::ALIAS);
    }

    public function save(Tag $tag): void
    {
        $this->em->wrapInTransaction(function () use ($tag): void {
            $this->em->persist($tag);
            $this->em->flush();
            $this->eventPublisher->publishAll($tag->releaseEvents());
        });
    }

    public function remove(Tag $tag): void
    {
        $this->em->wrapInTransaction(function () use ($tag): void {
            $this->em->remove($tag);
            $this->em->flush();
            $this->eventPublisher->publishAll($tag->releaseEvents());
        });
    }

    public function findById(IdentifierVO $id): ?Tag
    {
        return $this->em->find(Tag::class, $id->value());
    }

    public function findBySlug(Slug $slug): ?Tag
    {
        $tag = $this->query()
            ->andWhere(self::ALIAS . '.slug = :slug')
            ->setParameter('slug', $slug, SlugType::NAME)
            ->getQuery()
            ->getOneOrNullResult();

        return $tag instanceof Tag ? $tag : null;
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var list<Tag> $found */
        $found = $this->query()
            ->andWhere(self::ALIAS . '.id IN (:ids)')
            ->setParameter('ids', array_map(static fn (IdentifierVO $id): string => $id->toRfc4122(), $ids))
            ->getQuery()
            ->getResult();

        $byId = [];
        foreach ($found as $tag) {
            $byId[(string) $tag->id] = $tag;
        }

        $ordered = [];
        foreach ($ids as $id) {
            if (isset($byId[(string) $id])) {
                $ordered[] = $byId[(string) $id];
            }
        }

        return $ordered;
    }
}
