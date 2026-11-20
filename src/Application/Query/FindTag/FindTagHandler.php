<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTag;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

/**
 * @extends QuerySingleHandler<Tag>
 */
#[AsQueryHandler]
final readonly class FindTagHandler extends QuerySingleHandler
{
    public function __construct(TagRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function __invoke(FindTagQuery $query): Tag
    {
        return $this->build($query);
    }
}
