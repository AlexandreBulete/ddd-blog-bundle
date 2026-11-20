<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

final class UnknownCategoryException extends \DomainException
{
    /**
     * @param list<string> $ids
     */
    public function __construct(public readonly array $ids)
    {
        parent::__construct(sprintf('Unknown categor%s: %s.', count($ids) > 1 ? 'ies' : 'y', implode(', ', $ids)));
    }
}
