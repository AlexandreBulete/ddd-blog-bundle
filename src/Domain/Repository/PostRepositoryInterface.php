<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Repository;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * The narrowing methods return a narrowed copy, like filter(): they chain
 * (`visibleAt($now)->filedUnder($id)->withPagination(1, 10)`).
 *
 * @extends RepositoryInterface<Post>
 */
interface PostRepositoryInterface extends RepositoryInterface
{
    public function save(Post $post): void;

    public function remove(Post $post): void;

    public function findBySlug(Slug $slug): ?Post;

    /**
     * Only what the public may read at that instant, most recent first.
     */
    public function visibleAt(\DateTimeImmutable $now): static;

    public function filedUnder(CategoryId $categoryId): static;

    public function taggedWith(TagId $tagId): static;

    public function featuredOnly(): static;

    public function countFiledUnder(CategoryId $categoryId): int;

    public function countTaggedWith(TagId $tagId): int;

    /**
     * How many posts the public may read at that instant, per category; the
     * categories without any are left out.
     *
     * @return array<string, int> category id (string form) => visible posts
     */
    public function countVisibleByCategory(\DateTimeImmutable $now): array;

    /**
     * Visible posts closest to this one: sharing the most categories and
     * tags, then the most recent. Never the post itself.
     *
     * @param positive-int $limit
     *
     * @return list<Post>
     */
    public function relatedTo(Post $post, \DateTimeImmutable $now, int $limit): array;
}
