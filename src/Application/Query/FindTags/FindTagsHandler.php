<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTags;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends QueryCollectionHandler<Tag>
 */
#[AsQueryHandler]
final readonly class FindTagsHandler extends QueryCollectionHandler
{
    public function __construct(TagRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return RepositoryInterface<Tag>
     */
    public function __invoke(FindTagsQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
