<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\DraftPost;

use AlexandreBulete\DddBlogBundle\Application\Service\CategoryGuard;
use AlexandreBulete\DddBlogBundle\Application\Service\TagResolver;
use AlexandreBulete\DddBlogBundle\Domain\Exception\SlugAlreadyUsedException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\SlugGeneratorInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class DraftPostHandler
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private SlugGeneratorInterface $slugger,
        private CategoryGuard $categories,
        private TagResolver $tags,
        private ClockInterface $clock,
    ) {}

    public function __invoke(DraftPostCommand $command): Post
    {
        $slug = $command->slug ?? $this->slugger->slugify($command->title);
        if ($this->posts->findBySlug($slug) !== null) {
            throw new SlugAlreadyUsedException('post', $slug);
        }

        $now = $this->clock->now();
        $post = Post::draft(PostId::generate(), $command->title, $slug, $command->body, $command->excerpt, $command->authorId, $now);
        $post->categorize($this->categories->existing($command->categoryIds), $now);
        $post->tag($this->tags->resolve($command->tags, $now), $now);
        $this->posts->save($post);

        return $post;
    }
}
