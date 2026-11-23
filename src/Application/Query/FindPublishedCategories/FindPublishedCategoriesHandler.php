<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedCategories;

use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use Psr\Clock\ClockInterface;

#[AsQueryHandler]
final readonly class FindPublishedCategoriesHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private CategoryRepositoryInterface $categories,
        private ClockInterface $clock,
    ) {}

    /**
     * @return list<PublishedCategory>
     */
    public function __invoke(FindPublishedCategoriesQuery $query): array
    {
        $counts = $this->posts->countVisibleByCategory($this->clock->now());
        if ($counts === []) {
            return [];
        }

        $published = [];
        foreach ($this->categories->findByIds(array_map(CategoryId::fromString(...), array_map('strval', array_keys($counts)))) as $category) {
            $published[] = new PublishedCategory($category, $counts[(string) $category->id]);
        }
        usort($published, static fn (PublishedCategory $a, PublishedCategory $b): int => strcmp(mb_strtolower($a->category->name), mb_strtolower($b->category->name)));

        return $published;
    }
}
