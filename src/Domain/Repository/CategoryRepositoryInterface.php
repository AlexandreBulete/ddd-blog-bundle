<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Repository;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends RepositoryInterface<Category>
 */
interface CategoryRepositoryInterface extends RepositoryInterface
{
    public function save(Category $category): void;

    public function remove(Category $category): void;

    public function findBySlug(Slug $slug): ?Category;

    /**
     * In the order of the identifiers given; unknown ones are left out.
     *
     * @param list<CategoryId> $ids
     *
     * @return list<Category>
     */
    public function findByIds(array $ids): array;
}
