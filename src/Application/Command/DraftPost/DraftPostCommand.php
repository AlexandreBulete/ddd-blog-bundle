<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Command\DraftPost;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\AuthorId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Application\Command\CommandInterface;

/**
 * Starts a post. Nothing is public until it is published.
 *
 * @implements CommandInterface<Post>
 */
final readonly class DraftPostCommand implements CommandInterface
{
    /**
     * @param Slug|null $slug derived from the title when null
     * @param list<CategoryId> $categoryIds
     * @param list<string> $tags tag names, created on the fly
     */
    public function __construct(
        public string $title,
        public string $body = '',
        public ?string $excerpt = null,
        public ?Slug $slug = null,
        public ?AuthorId $authorId = null,
        public array $categoryIds = [],
        public array $tags = [],
    ) {}
}
