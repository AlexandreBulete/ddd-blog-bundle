<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;

/**
 * One row of blog_post_tag: a read index of Post::$tagIds, rewritten by the
 * post repository on every save. Never loaded as an object; mapped so that
 * DQL can query it and the schema tools know the table.
 */
final class PostTagLink
{
    public function __construct(
        public readonly PostId $postId,
        public readonly TagId $tagId,
    ) {}
}
