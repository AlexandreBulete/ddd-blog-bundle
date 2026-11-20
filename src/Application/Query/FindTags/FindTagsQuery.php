<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindTags;

use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * @implements QueryInterface<TagRepositoryInterface>
 */
final readonly class FindTagsQuery implements QueryInterface
{
    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string> $withSorting
     */
    public function __construct(
        public ?int $page = null,
        public ?int $itemsPerPage = null,
        public array $criteria = [],
        public array $withSorting = [],
    ) {}
}
