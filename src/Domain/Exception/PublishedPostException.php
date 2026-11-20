<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Exception;

/**
 * What the public reads, or will at a scheduled date, is unpublished or
 * archived before it is deleted: a link out there must not break by
 * accident.
 */
final class PublishedPostException extends \DomainException
{
    public function __construct(string $message = 'A published post cannot be deleted: unpublish or archive it first.')
    {
        parent::__construct($message);
    }
}
