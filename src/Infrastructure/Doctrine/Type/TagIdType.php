<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class TagIdType extends GuidType
{
    public const string NAME = 'blog_tag_id';

    protected string $name = self::NAME;
    protected string $voClass = TagId::class;
}
