<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\TagPost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Tags the post with exactly these names; unknown tags are created.
 *
 * @implements CommandInterface<Post>
 */
final readonly class TagPostCommand implements CommandInterface
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public PostId $id,
        public array $tags,
    ) {}
}
