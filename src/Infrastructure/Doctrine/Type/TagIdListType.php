<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;

/**
 * @extends IdentifierListType<TagId>
 */
final class TagIdListType extends IdentifierListType
{
    public const string NAME = 'blog_tag_ids';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function identifierClass(): string
    {
        return TagId::class;
    }
}
