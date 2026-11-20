<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class CategoryIdType extends GuidType
{
    public const string NAME = 'blog_category_id';

    protected string $name = self::NAME;
    protected string $voClass = CategoryId::class;
}
