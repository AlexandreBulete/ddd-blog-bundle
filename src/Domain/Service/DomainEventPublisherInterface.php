<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Service;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * Where the repositories hand the events an aggregate recorded, once it is
 * written, in the same transaction.
 */
interface DomainEventPublisherInterface
{
    /**
     * @param iterable<DomainEvent> $events
     */
    public function publishAll(iterable $events): void;
}
