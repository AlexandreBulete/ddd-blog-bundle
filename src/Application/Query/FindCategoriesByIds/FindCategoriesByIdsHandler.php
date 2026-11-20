<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindCategoriesByIds;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindCategoriesByIdsHandler
{
    public function __construct(
        private CategoryRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Category>
     */
    public function __invoke(FindCategoriesByIdsQuery $query): array
    {
        return $this->repository->findByIds($query->ids);
    }
}
