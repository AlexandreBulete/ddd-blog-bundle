<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RemoveTag;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Refused while posts carry it: untag them first.
 *
 * @implements CommandInterface<void>
 */
final readonly class RemoveTagCommand implements CommandInterface
{
    public function __construct(
        public TagId $id,
    ) {}
}
