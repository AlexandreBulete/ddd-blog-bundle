<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedCategories;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;

final readonly class PublishedCategory
{
    public function __construct(
        public Category $category,
        public int $posts,
    ) {}
}
