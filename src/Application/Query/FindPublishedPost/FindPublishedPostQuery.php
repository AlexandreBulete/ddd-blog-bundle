<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedPost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * One post the public may read now. A draft, a scheduled or an archived post
 * is not found: the public must not learn it exists.
 *
 * @implements QueryInterface<Post>
 */
#[PublicAccess]
final readonly class FindPublishedPostQuery implements QueryInterface
{
    public function __construct(
        public Slug $slug,
    ) {}
}
