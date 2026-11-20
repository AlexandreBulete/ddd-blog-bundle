<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindCategories;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends QueryCollectionHandler<Category>
 */
#[AsQueryHandler]
final readonly class FindCategoriesHandler extends QueryCollectionHandler
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return RepositoryInterface<Category>
     */
    public function __invoke(FindCategoriesQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
