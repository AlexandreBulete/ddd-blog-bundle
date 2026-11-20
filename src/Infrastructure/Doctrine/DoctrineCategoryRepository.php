<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\SlugType;
use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<Category>
 */
final class DoctrineCategoryRepository extends DoctrineRepository implements CategoryRepositoryInterface
{
    private const string ALIAS = 'category';

    public function __construct(
        EntityManagerInterface $em,
        private readonly DomainEventPublisherInterface $eventPublisher,
    ) {
        parent::__construct($em, Category::class, self::ALIAS);
    }

    public function save(Category $category): void
    {
        $this->em->wrapInTransaction(function () use ($category): void {
            $this->em->persist($category);
            $this->em->flush();
            $this->eventPublisher->publishAll($category->releaseEvents());
        });
    }

    public function remove(Category $category): void
    {
        $this->em->wrapInTransaction(function () use ($category): void {
            $this->em->remove($category);
            $this->em->flush();
            $this->eventPublisher->publishAll($category->releaseEvents());
        });
    }

    public function findById(IdentifierVO $id): ?Category
    {
        return $this->em->find(Category::class, $id->value());
    }

    public function findBySlug(Slug $slug): ?Category
    {
        $category = $this->query()
            ->andWhere(self::ALIAS . '.slug = :slug')
            ->setParameter('slug', $slug, SlugType::NAME)
            ->getQuery()
            ->getOneOrNullResult();

        return $category instanceof Category ? $category : null;
    }

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var list<Category> $found */
        $found = $this->query()
            ->andWhere(self::ALIAS . '.id IN (:ids)')
            ->setParameter('ids', array_map(static fn (IdentifierVO $id): string => $id->toRfc4122(), $ids))
            ->getQuery()
            ->getResult();

        $byId = [];
        foreach ($found as $category) {
            $byId[(string) $category->id] = $category;
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
