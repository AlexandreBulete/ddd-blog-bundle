<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RemoveCategory;

use AlexandreBulete\DddBlogBundle\Domain\Exception\StillInUseException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;

#[AsCommandHandler]
final readonly class RemoveCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private PostRepositoryInterface $posts,
    ) {}

    public function __invoke(RemoveCategoryCommand $command): void
    {
        $category = $this->categories->findById($command->id)
            ?? throw new EntityNotFoundException(Category::class, $command->id);

        $posts = $this->posts->countFiledUnder($category->id);
        if ($posts > 0) {
            throw new StillInUseException('category', $category->name, $posts);
        }

        $category->remove();
        $this->categories->remove($category);
    }
}
