<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTagsByIds;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The tags of a post, in the post's order.
 *
 * @implements QueryInterface<list<Tag>>
 */
#[PublicAccess]
final readonly class FindTagsByIdsQuery implements QueryInterface
{
    /**
     * @param list<TagId> $ids
     */
    public function __construct(
        public array $ids,
    ) {}
}
