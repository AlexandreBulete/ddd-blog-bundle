<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class PostSlugChanged implements DomainEvent
{
    public function __construct(
        public string $postId,
        public string $slug,
        public string $previousSlug,
    ) {}
}
