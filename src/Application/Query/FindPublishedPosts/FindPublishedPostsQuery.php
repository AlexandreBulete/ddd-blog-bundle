<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPosts;

use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * What the public may read now, most recent first: a category page, a tag
 * page, the featured posts. Resolve a slug with FindCategoryBySlug first, so
 * that an unknown category is a 404 rather than an empty page.
 *
 * @implements QueryInterface<PostRepositoryInterface>
 */
#[PublicAccess]
final readonly class FindPublishedPostsQuery implements QueryInterface
{
    public function __construct(
        public int $page = 1,
        public int $itemsPerPage = 10,
        public ?CategoryId $categoryId = null,
        public ?TagId $tagId = null,
        public bool $featuredOnly = false,
    ) {}
}
