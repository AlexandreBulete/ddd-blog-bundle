<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddDoctrineBridge\Type\VarcharType;

final class SlugType extends VarcharType
{
    public const string NAME = 'blog_slug';

    protected string $name = self::NAME;
    protected int $length = Slug::MAX_LENGTH;
    protected string $voClass = Slug::class;
}
