<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RenameTag;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;

#[AsCommandHandler]
final readonly class RenameTagHandler
{
    public function __construct(
        private TagRepositoryInterface $tags,
    ) {}

    public function __invoke(RenameTagCommand $command): Tag
    {
        $tag = $this->tags->findById($command->id)
            ?? throw new EntityNotFoundException(Tag::class, $command->id);

        $tag->rename($command->name);
        $this->tags->save($tag);

        return $tag;
    }
}
