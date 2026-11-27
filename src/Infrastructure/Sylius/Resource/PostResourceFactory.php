<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddBlogBundle\Application\Query\FindTagsByIds\FindTagsByIdsQuery;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Infrastructure\Media\PostVisuals;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Psr\Clock\ClockInterface;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\PublicPages;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Builds PostResources with their tag names, read through the query bus, and
 * their status label translated (a grid string cell prints as is).
 */
final readonly class PostResourceFactory
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private ClockInterface $clock,
        private TranslatorInterface $translator,
        private PublicPages $pages,
        private ?PostVisuals $visuals = null,
    ) {}

    public function fromModel(Post $post): PostResource
    {
        $names = [];
        if ($post->tagIds !== []) {
            /** @var list<Tag> $tags */
            $tags = $this->queryBus->ask(new FindTagsByIdsQuery($post->tagIds));
            $names = array_map(static fn (Tag $tag): string => $tag->name, $tags);
        }

        $resource = PostResource::fromModel($post, $names, $this->clock->now());
        if ($post->isVisibleAt($this->clock->now())) {
            $resource->publicUrl = $this->pages->url('post', $post->slug->value());
        }

        return $this->pictured($this->labelled($resource), $post);
    }

    /**
     * For the grid: no tags shown, so none loaded; the visuals of the page in
     * one query.
     *
     * @param iterable<Post> $posts
     *
     * @return list<PostResource>
     */
    public function forList(iterable $posts): array
    {
        $posts = iterator_to_array($posts, false);
        $this->visuals?->preload($posts);

        return array_map(fn (Post $post): PostResource => $this->pictured($this->labelled(PostResource::fromModel($post, [], $this->clock->now())), $post), $posts);
    }

    private function labelled(PostResource $resource): PostResource
    {
        $resource->statusLabel = $this->translator->trans((string) $resource->statusLabel);

        return $resource;
    }

    /**
     * A thumbnail of the visual, for the grid and above the upload field.
     */
    private function pictured(PostResource $resource, Post $post): PostResource
    {
        $resource->visualUrl = $this->visuals?->thumbnailUrl($post);

        return $resource;
    }
}
