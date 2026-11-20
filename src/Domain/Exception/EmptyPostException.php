<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

/**
 * A post with no body cannot be read by the public: it is neither published,
 * nor emptied once published.
 */
final class EmptyPostException extends \DomainException
{
    public function __construct(string $message = 'An empty post cannot be published.')
    {
        parent::__construct($message);
    }
}
