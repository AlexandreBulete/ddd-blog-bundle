<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class PostUnpublished implements DomainEvent
{
    public function __construct(
        public string $postId,
    ) {}
}
