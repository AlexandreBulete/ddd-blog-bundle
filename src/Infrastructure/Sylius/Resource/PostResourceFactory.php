<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddBlogBundle\Application\Query\FindTagsByIds\FindTagsByIdsQuery;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Psr\Clock\ClockInterface;

/**
 * Builds PostResources with their tag names, read through the query bus.
 */
final readonly class PostResourceFactory
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private ClockInterface $clock,
    ) {}

    public function fromModel(Post $post): PostResource
    {
        $names = [];
        if ($post->tagIds !== []) {
            /** @var list<Tag> $tags */
            $tags = $this->queryBus->ask(new FindTagsByIdsQuery($post->tagIds));
            $names = array_map(static fn (Tag $tag): string => $tag->name, $tags);
        }

        return PostResource::fromModel($post, $names, $this->clock->now());
    }

    /**
     * For the grid: no tags shown, so none loaded.
     */
    public function forList(Post $post): PostResource
    {
        return PostResource::fromModel($post, [], $this->clock->now());
    }
}
