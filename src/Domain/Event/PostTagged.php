<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

final readonly class PostTagged implements DomainEvent
{
    /**
     * @param list<string> $tagIds
     */
    public function __construct(
        public string $postId,
        public array $tagIds,
    ) {}
}
