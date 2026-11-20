<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPosts;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends QueryCollectionHandler<Post>
 */
#[AsQueryHandler]
final readonly class FindPostsHandler extends QueryCollectionHandler
{
    public function __construct(PostRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return RepositoryInterface<Post>
     */
    public function __invoke(FindPostsQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
