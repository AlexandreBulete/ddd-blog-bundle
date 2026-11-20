<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RemoveCategory;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Refused while posts are filed under it: recategorize them first.
 *
 * @implements CommandInterface<void>
 */
final readonly class RemoveCategoryCommand implements CommandInterface
{
    public function __construct(
        public CategoryId $id,
    ) {}
}
