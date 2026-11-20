<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\CategorizePost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Files the post under exactly these categories, the first one being the main
 * one. An empty list removes them all.
 *
 * @implements CommandInterface<Post>
 */
final readonly class CategorizePostCommand implements CommandInterface
{
    /**
     * @param list<CategoryId> $categoryIds
     */
    public function __construct(
        public PostId $id,
        public array $categoryIds,
    ) {}
}
