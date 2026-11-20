<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTag;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * @implements QueryInterface<Tag>
 */
final readonly class FindTagQuery implements QueryInterface
{
    public function __construct(
        public TagId $id,
    ) {}
}
