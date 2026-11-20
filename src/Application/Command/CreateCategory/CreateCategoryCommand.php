<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\CreateCategory;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * The slug is derived from the name when none is given.
 *
 * @implements CommandInterface<Category>
 */
final readonly class CreateCategoryCommand implements CommandInterface
{
    public function __construct(
        public string $name,
        public ?Slug $slug = null,
        public ?string $description = null,
    ) {}
}
