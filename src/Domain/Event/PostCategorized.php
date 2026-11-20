<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class PostCategorized implements DomainEvent
{
    /**
     * @param list<string> $categoryIds
     */
    public function __construct(
        public string $postId,
        public array $categoryIds,
    ) {}
}
