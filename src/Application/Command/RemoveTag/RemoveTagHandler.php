<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RemoveTag;

use AlexandreBulete\DddBlogBundle\Domain\Exception\StillInUseException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;

#[AsCommandHandler]
final readonly class RemoveTagHandler
{
    public function __construct(
        private TagRepositoryInterface $tags,
        private PostRepositoryInterface $posts,
    ) {}

    public function __invoke(RemoveTagCommand $command): void
    {
        $tag = $this->tags->findById($command->id)
            ?? throw new EntityNotFoundException(Tag::class, $command->id);

        $posts = $this->posts->countTaggedWith($tag->id);
        if ($posts > 0) {
            throw new StillInUseException('tag', $tag->name, $posts);
        }

        $tag->remove();
        $this->tags->remove($tag);
    }
}
