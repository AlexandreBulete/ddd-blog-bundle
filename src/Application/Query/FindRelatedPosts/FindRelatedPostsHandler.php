<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindRelatedPosts;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsQueryHandler]
final readonly class FindRelatedPostsHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private ClockInterface $clock,
    ) {}

    /**
     * @return list<Post>
     */
    public function __invoke(FindRelatedPostsQuery $query): array
    {
        $post = $this->posts->findById($query->id)
            ?? throw new EntityNotFoundException(Post::class, $query->id);

        return $this->posts->relatedTo($post, $this->clock->now(), $query->limit);
    }
}
