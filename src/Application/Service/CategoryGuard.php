<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Service;

use AlexandreBulete\DddBlogBundle\Domain\Exception\UnknownCategoryException;
use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;

/**
 * Checks that the categories a post is filed under exist, before the post
 * references them: the post stores identifiers only, nothing else would
 * notice a typo or a category removed in the meantime.
 */
final readonly class CategoryGuard
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @param list<CategoryId> $ids
     *
     * @return list<CategoryId> the same identifiers, in the same order
     *
     * @throws UnknownCategoryException
     */
    public function existing(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $found = [];
        foreach ($this->categories->findByIds($ids) as $category) {
            $found[(string) $category->id] = true;
        }

        $unknown = [];
        foreach ($ids as $id) {
            if (!isset($found[(string) $id])) {
                $unknown[] = (string) $id;
            }
        }
        if ($unknown !== []) {
            throw new UnknownCategoryException(array_values(array_unique($unknown)));
        }

        return $ids;
    }
}
