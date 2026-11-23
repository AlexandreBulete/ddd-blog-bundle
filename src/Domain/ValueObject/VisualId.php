<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\ValueObject;

use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;

/**
 * The picture heading a post: an identifier from whatever keeps the
 * application's media (a media library, typically). No foreign key, no
 * lookup from here: the blog only knows that a post has a visual.
 */
final readonly class VisualId extends IdentifierVO
{
}
