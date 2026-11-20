<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPost;

use AlexandreBulete\DddBlogBundle\Domain\Exception\NotFoundBySlugException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use Psr\Clock\ClockInterface;

#[AsQueryHandler]
final readonly class FindPublishedPostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private ClockInterface $clock,
    ) {}

    public function __invoke(FindPublishedPostQuery $query): Post
    {
        $post = $this->posts->findBySlug($query->slug);
        if ($post === null || !$post->isVisibleAt($this->clock->now())) {
            throw new NotFoundBySlugException('post', $query->slug);
        }

        return $post;
    }
}
