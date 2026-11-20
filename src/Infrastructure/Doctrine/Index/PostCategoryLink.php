<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;

/**
 * One row of blog_post_category: a read index of Post::$categoryIds, rewritten by the
 * post repository on every save. Never loaded as an object; mapped so that
 * DQL can query it and the schema tools know the table.
 */
final class PostCategoryLink
{
    public function __construct(
        public readonly PostId $postId,
        public readonly CategoryId $categoryId,
    ) {}
}
