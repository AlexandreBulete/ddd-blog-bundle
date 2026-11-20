<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\CreateCategory;

use AlexandreBulete\DddBlogBundle\Domain\Exception\SlugAlreadyUsedException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\SlugGeneratorInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private SlugGeneratorInterface $slugger,
        private ClockInterface $clock,
    ) {}

    public function __invoke(CreateCategoryCommand $command): Category
    {
        $slug = $command->slug ?? $this->slugger->slugify($command->name);
        if ($this->categories->findBySlug($slug) !== null) {
            throw new SlugAlreadyUsedException('category', $slug);
        }

        $category = Category::create(CategoryId::generate(), $command->name, $slug, $command->description, $this->clock->now());
        $this->categories->save($category);

        return $category;
    }
}
