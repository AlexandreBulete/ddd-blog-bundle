<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\CategorizePost;

use AlexandreBulete\DddBlogBundle\Application\Service\CategoryGuard;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class CategorizePostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private CategoryGuard $categories,
        private ClockInterface $clock,
    ) {}

    public function __invoke(CategorizePostCommand $command): Post
    {
        $post = $this->posts->findById($command->id)
            ?? throw new EntityNotFoundException(Post::class, $command->id);

        $post->categorize($this->categories->existing($command->categoryIds), $this->clock->now());
        $this->posts->save($post);

        return $post;
    }
}
