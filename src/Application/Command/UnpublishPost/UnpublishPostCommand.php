<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\UnpublishPost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * @implements CommandInterface<Post>
 */
final readonly class UnpublishPostCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
    ) {}
}
