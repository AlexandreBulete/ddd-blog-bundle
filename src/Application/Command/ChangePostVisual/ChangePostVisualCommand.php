<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\ChangePostVisual;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\VisualId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Sets the picture heading a post, or takes it off (null).
 *
 * @implements CommandInterface<Post>
 */
final readonly class ChangePostVisualCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
        public ?VisualId $visual,
    ) {}
}
