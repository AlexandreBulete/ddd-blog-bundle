<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

/**
 * @extends QuerySingleHandler<Post>
 */
#[AsQueryHandler]
final readonly class FindPostHandler extends QuerySingleHandler
{
    public function __construct(PostRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function __invoke(FindPostQuery $query): Post
    {
        return $this->build($query);
    }
}
