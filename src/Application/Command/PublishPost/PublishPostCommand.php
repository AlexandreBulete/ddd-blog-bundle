<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\PublishPost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Publishes now, or schedules for $publishAt. Publishing a published post
 * moves its date.
 *
 * @implements CommandInterface<Post>
 */
final readonly class PublishPostCommand implements CommandInterface
{
    public function __construct(
        public PostId $id,
        public ?\DateTimeImmutable $publishAt = null,
    ) {}
}
