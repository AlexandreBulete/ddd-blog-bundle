<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindCategory;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

/**
 * @extends QuerySingleHandler<Category>
 */
#[AsQueryHandler]
final readonly class FindCategoryHandler extends QuerySingleHandler
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function __invoke(FindCategoryQuery $query): Category
    {
        return $this->build($query);
    }
}
