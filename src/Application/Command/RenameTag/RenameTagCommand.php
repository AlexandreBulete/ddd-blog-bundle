<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\RenameTag;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * The name only: the slug, and links to the tag page, stay.
 *
 * @implements CommandInterface<Tag>
 */
final readonly class RenameTagCommand implements CommandInterface
{
    public function __construct(
        public TagId $id,
        public string $name,
    ) {}
}
