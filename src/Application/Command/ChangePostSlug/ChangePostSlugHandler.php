<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\ChangePostSlug;

use AlexandreBulete\DddBlogBundle\Domain\Exception\SlugAlreadyUsedException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class ChangePostSlugHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangePostSlugCommand $command): Post
    {
        $post = $this->posts->findById($command->id)
            ?? throw new EntityNotFoundException(Post::class, $command->id);

        $holder = $this->posts->findBySlug($command->slug);
        if ($holder !== null && !$holder->id->equals($post->id)) {
            throw new SlugAlreadyUsedException('post', $command->slug);
        }

        $post->changeSlug($command->slug, $this->clock->now());
        $this->posts->save($post);

        return $post;
    }
}
