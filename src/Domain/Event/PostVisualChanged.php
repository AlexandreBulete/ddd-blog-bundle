<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * A visual set, replaced or taken off. The previous one is given so that its
 * owner can let it go.
 */
final readonly class PostVisualChanged implements DomainEvent
{
    public function __construct(
        public string $postId,
        public ?string $visualId,
        public ?string $previousVisualId,
    ) {}
}
