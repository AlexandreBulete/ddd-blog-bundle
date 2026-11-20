<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Repository;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends RepositoryInterface<Tag>
 */
interface TagRepositoryInterface extends RepositoryInterface
{
    public function save(Tag $tag): void;

    public function remove(Tag $tag): void;

    public function findBySlug(Slug $slug): ?Tag;

    /**
     * In the order of the identifiers given; unknown ones are left out.
     *
     * @param list<TagId> $ids
     *
     * @return list<Tag>
     */
    public function findByIds(array $ids): array;
}
