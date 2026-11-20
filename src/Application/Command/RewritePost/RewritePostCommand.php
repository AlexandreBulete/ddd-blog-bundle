<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RewritePost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * @implements CommandInterface<Post>
 */
final readonly class RewritePostCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
        public string $body,
        public ?string $excerpt = null,
    ) {}
}
