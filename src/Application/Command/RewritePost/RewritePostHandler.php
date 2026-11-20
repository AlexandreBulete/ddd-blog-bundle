<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RewritePost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class RewritePostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private ClockInterface $clock,
    ) {}

    public function __invoke(RewritePostCommand $command): Post
    {
        $post = $this->posts->findById($command->id)
            ?? throw new EntityNotFoundException(Post::class, $command->id);

        $post->rewrite($command->body, $command->excerpt, $this->clock->now());
        $this->posts->save($post);

        return $post;
    }
}
