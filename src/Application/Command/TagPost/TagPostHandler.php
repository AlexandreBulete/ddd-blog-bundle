<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\TagPost;

use AlexandreBulete\DddBlogBundle\Application\Service\TagResolver;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class TagPostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private TagResolver $tags,
        private ClockInterface $clock,
    ) {}

    public function __invoke(TagPostCommand $command): Post
    {
        $post = $this->posts->findById($command->id)
            ?? throw new EntityNotFoundException(Post::class, $command->id);

        $post->tag($this->tags->resolve($command->tags, $this->clock->now()), $this->clock->now());
        $this->posts->save($post);

        return $post;
    }
}
