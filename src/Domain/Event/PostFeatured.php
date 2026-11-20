<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class PostFeatured implements DomainEvent
{
    public function __construct(
        public string $postId,
    ) {}
}
