<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\ArchivePost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * @implements CommandInterface<Post>
 */
final readonly class ArchivePostCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
    ) {}
}
