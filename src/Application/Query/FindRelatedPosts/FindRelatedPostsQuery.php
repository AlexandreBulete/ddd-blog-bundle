<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindRelatedPosts;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * "If you liked it, read also": visible posts sharing the most categories and
 * tags with this one.
 *
 * @implements QueryInterface<list<Post>>
 */
#[PublicAccess]
final readonly class FindRelatedPostsQuery implements QueryInterface
{
    /**
     * @param positive-int $limit
     */
    public function __construct(
        public PostId $id,
        public int $limit = 3,
    ) {}
}
