<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\AuthorId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class AuthorIdType extends GuidType
{
    public const string NAME = 'blog_author_id';

    protected string $name = self::NAME;
    protected string $voClass = AuthorId::class;
}
