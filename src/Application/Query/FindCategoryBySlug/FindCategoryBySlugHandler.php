<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindCategoryBySlug;

use AlexandreBulete\DddBlogBundle\Domain\Exception\NotFoundBySlugException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindCategoryBySlugHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
    ) {}

    public function __invoke(FindCategoryBySlugQuery $query): Category
    {
        return $this->repository->findBySlug($query->slug)
            ?? throw new NotFoundBySlugException('category', $query->slug);
    }
}
