<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\DeletePost;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Only a post the public cannot read, nor will at a scheduled date.
 *
 * @implements CommandInterface<void>
 */
final readonly class DeletePostCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
    ) {}
}
