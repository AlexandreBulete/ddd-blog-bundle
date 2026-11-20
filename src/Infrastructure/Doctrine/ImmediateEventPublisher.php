<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddBlogBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddFoundation\Application\Event\EventDispatcherInterface;

/**
 * Default adapter for {@see DomainEventPublisherInterface}: hands the events
 * to the application dispatcher, inside the repository's transaction.
 *
 * Fine for in-process subscribers (a cache to clear, a sitemap to rebuild).
 * For delivery outside the process, bind an outbox-backed adapter onto the
 * interface instead: from here, a broker would hear about a write that a
 * rollback may still undo.
 */
final readonly class ImmediateEventPublisher implements DomainEventPublisherInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function publishAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
