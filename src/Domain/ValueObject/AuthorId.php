<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\ValueObject;

use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;

/**
 * Who wrote a post: an identifier from the application's identity context
 * (an IAM user id, typically). No foreign key, no lookup from here: the blog
 * does not know who users are, only that a post has an author.
 */
final readonly class AuthorId extends IdentifierVO
{
}
