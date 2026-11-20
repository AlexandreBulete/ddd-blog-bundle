<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\ChangePostSlug;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * @implements CommandInterface<Post>
 */
final readonly class ChangePostSlugCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
        public Slug $slug,
    ) {}
}
