<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\FeaturePost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class FeaturePostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private ClockInterface $clock,
    ) {}

    public function __invoke(FeaturePostCommand $command): Post
    {
        $post = $this->posts->findById($command->id)
            ?? throw new EntityNotFoundException(Post::class, $command->id);

        $post->feature($this->clock->now());
        $this->posts->save($post);

        return $post;
    }
}
