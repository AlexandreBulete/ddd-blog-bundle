<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindCategoryBySlug;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * @implements QueryInterface<Category>
 */
#[PublicAccess]
final readonly class FindCategoryBySlugQuery implements QueryInterface
{
    public function __construct(
        public Slug $slug,
    ) {}
}
