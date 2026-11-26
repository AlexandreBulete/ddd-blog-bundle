<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;

/**
 * One row of blog_post_search: the post's words as the search looks them up
 * (see SearchText), title apart so that a match there ranks first. Rewritten
 * by the post repository on every save. Never loaded as an object; mapped so
 * that DQL can query it and the schema tools know the table.
 */
final class PostSearchText
{
    public function __construct(
        public readonly PostId $postId,
        public readonly string $title,
        public readonly string $content,
    ) {}
}
