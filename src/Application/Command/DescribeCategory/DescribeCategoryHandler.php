<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\DescribeCategory;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class DescribeCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ClockInterface $clock,
    ) {}

    public function __invoke(DescribeCategoryCommand $command): Category
    {
        $category = $this->categories->findById($command->id)
            ?? throw new EntityNotFoundException(Category::class, $command->id);

        $category->describe($command->description, $this->clock->now());
        $this->categories->save($category);

        return $category;
    }
}
