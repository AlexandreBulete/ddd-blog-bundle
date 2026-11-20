<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTagsByIds;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindTagsByIdsHandler
{
    public function __construct(
        private TagRepositoryInterface $repository,
    ) {}

    /**
     * @return list<Tag>
     */
    public function __invoke(FindTagsByIdsQuery $query): array
    {
        return $this->repository->findByIds($query->ids);
    }
}
