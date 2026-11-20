<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

/**
 * An archived post is out of the public eye; highlighting it would
 * contradict that.
 */
final class ArchivedPostException extends \DomainException
{
    public function __construct(string $message = 'An archived post cannot be featured.')
    {
        parent::__construct($message);
    }
}
