<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;

/**
 * @extends IdentifierListType<CategoryId>
 */
final class CategoryIdListType extends IdentifierListType
{
    public const string NAME = 'blog_category_ids';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function identifierClass(): string
    {
        return CategoryId::class;
    }
}
