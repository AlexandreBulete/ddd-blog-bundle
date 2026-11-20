<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindCategoriesByIds;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The categories of a post, in the post's order.
 *
 * @implements QueryInterface<list<Category>>
 */
#[PublicAccess]
final readonly class FindCategoriesByIdsQuery implements QueryInterface
{
    /**
     * @param list<CategoryId> $ids
     */
    public function __construct(
        public array $ids,
    ) {}
}
