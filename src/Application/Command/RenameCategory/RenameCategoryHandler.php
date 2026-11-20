<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RenameCategory;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class RenameCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ClockInterface $clock,
    ) {}

    public function __invoke(RenameCategoryCommand $command): Category
    {
        $category = $this->categories->findById($command->id)
            ?? throw new EntityNotFoundException(Category::class, $command->id);

        $category->rename($command->name, $this->clock->now());
        $this->categories->save($category);

        return $category;
    }
}
