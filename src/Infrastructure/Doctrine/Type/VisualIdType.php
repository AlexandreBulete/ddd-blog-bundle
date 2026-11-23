<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\VisualId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class VisualIdType extends GuidType
{
    public const string NAME = 'blog_visual_id';

    protected string $name = self::NAME;
    protected string $voClass = VisualId::class;
}
