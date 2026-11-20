<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddDoctrineBridge\Type\GuidType;

final class PostIdType extends GuidType
{
    public const string NAME = 'blog_post_id';

    protected string $name = self::NAME;
    protected string $voClass = PostId::class;
}
