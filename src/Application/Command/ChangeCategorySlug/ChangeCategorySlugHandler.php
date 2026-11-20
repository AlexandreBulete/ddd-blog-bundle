<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\ChangeCategorySlug;

use AlexandreBulete\DddBlogBundle\Domain\Exception\SlugAlreadyUsedException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class ChangeCategorySlugHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangeCategorySlugCommand $command): Category
    {
        $category = $this->categories->findById($command->id)
            ?? throw new EntityNotFoundException(Category::class, $command->id);

        $holder = $this->categories->findBySlug($command->slug);
        if ($holder !== null && !$holder->id->equals($category->id)) {
            throw new SlugAlreadyUsedException('category', $command->slug);
        }

        $category->changeSlug($command->slug, $this->clock->now());
        $this->categories->save($category);

        return $category;
    }
}
