<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Tests\Integration\Fixture;

use AlexandreBulete\DddBlogBundle\Domain\Service\DomainEventPublisherInterface;

final class NullEventPublisher implements DomainEventPublisherInterface
{
    public function publishAll(iterable $events): void
    {
        foreach ($events as $event) {
            // Drained, like a real publisher would.
        }
    }
}
