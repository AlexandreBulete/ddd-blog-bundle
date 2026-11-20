<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTagBySlug;

use AlexandreBulete\DddBlogBundle\Domain\Exception\NotFoundBySlugException;
use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindTagBySlugHandler
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    public function __invoke(FindTagBySlugQuery $query): Tag
    {
        return $this->repository->findBySlug($query->slug)
            ?? throw new NotFoundBySlugException('tag', $query->slug);
    }
}
