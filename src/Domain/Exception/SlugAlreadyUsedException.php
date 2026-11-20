<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;

final class SlugAlreadyUsedException extends \DomainException
{
    public function __construct(
        public readonly string $kind,
        public readonly Slug $slug,
    ) {
        parent::__construct(sprintf('Another %s already uses the slug "%s".', $kind, $slug->value()));
    }
}
