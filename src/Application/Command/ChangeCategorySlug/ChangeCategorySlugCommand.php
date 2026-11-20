<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\ChangeCategorySlug;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * @implements CommandInterface<Category>
 */
final readonly class ChangeCategorySlugCommand implements CommandInterface
{
    public function __construct(
        public CategoryId $id,
        public Slug $slug,
    ) {}
}
