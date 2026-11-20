<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\DeletePost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;

#[AsCommandHandler]
final readonly class DeletePostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
    ) {}

    public function __invoke(DeletePostCommand $command): void
    {
        $post = $this->posts->findById($command->id)
            ?? throw new EntityNotFoundException(Post::class, $command->id);

        $post->delete();
        $this->posts->remove($post);
    }
}
