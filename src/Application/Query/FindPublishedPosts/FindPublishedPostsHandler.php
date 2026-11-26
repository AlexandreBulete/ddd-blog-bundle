<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPosts;

use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use Psr\Clock\ClockInterface;

#[AsQueryHandler]
final readonly class FindPublishedPostsHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private ClockInterface $clock,
    ) {}

    public function __invoke(FindPublishedPostsQuery $query): PostRepositoryInterface
    {
        $posts = $this->posts->visibleAt($this->clock->now());
        if ($query->categoryId !== null) {
            $posts = $posts->filedUnder($query->categoryId);
        }
        if ($query->tagId !== null) {
            $posts = $posts->taggedWith($query->tagId);
        }
        if ($query->search !== null && trim($query->search) !== '') {
            $posts = $posts->matching($query->search);
        }
        if ($query->featuredOnly) {
            $posts = $posts->featuredOnly();
        }

        return $posts->withPagination(max(1, $query->page), max(1, $query->itemsPerPage));
    }
}
