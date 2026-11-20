<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;

/**
 * Nothing the public may see answers to this slug: unknown, or not visible
 * yet. One exception for both, so the public side cannot tell them apart.
 */
final class NotFoundBySlugException extends \DomainException
{
    public function __construct(
        public readonly string $kind,
        public readonly Slug $slug,
    ) {
        parent::__construct(sprintf('No %s found for the slug "%s".', $kind, $slug->value()));
    }
}
