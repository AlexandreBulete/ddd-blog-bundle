<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RenameCategory;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * @implements CommandInterface<Category>
 */
final readonly class RenameCategoryCommand implements CommandInterface
{
    public function __construct(
        public CategoryId $id,
        public string $name,
    ) {}
}
