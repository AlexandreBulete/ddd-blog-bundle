<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

/**
 * A category or a tag that posts still carry: removing it would change those
 * posts behind their author's back. Recategorize or untag them first.
 */
final class StillInUseException extends \DomainException
{
    public function __construct(
        public readonly string $kind,
        public readonly string $name,
        public readonly int $posts,
    ) {
        parent::__construct(sprintf('The %s "%s" is still used by %d post(s).', $kind, $name, $posts));
    }
}
