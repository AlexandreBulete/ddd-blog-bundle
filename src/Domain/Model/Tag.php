<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Model;

use AlexandreBulete\DddBlogBundle\Domain\Event\TagCreated;
use AlexandreBulete\DddBlogBundle\Domain\Event\TagRemoved;
use AlexandreBulete\DddBlogBundle\Domain\Event\TagRenamed;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * A keyword. Created the first time a post is tagged with it — nobody fills a
 * tag list before writing — and identified by its slug: "Polar" and "polar"
 * are one tag.
 */
final class Tag
{
    use RecordsEvents;

    public const int NAME_MAX_LENGTH = 60;

    private function __construct(
        private(set) TagId $id,
        private(set) string $name,
        private(set) Slug $slug,
        private(set) \DateTimeImmutable $createdAt,
    ) {}

    public static function create(TagId $id, string $name, Slug $slug, \DateTimeImmutable $at): self
    {
        $tag = new self($id, self::assertName($name), $slug, $at);
        $tag->recordEvent(new TagCreated((string) $id, $tag->name, $slug->value()));

        return $tag;
    }

    /**
     * The name only: the slug stays, so that links to the tag page keep
     * working after a typo is fixed.
     */
    public function rename(string $name): void
    {
        $name = self::assertName($name);
        if ($name === $this->name) {
            return;
        }

        $this->name = $name;
        $this->recordEvent(new TagRenamed((string) $this->id, $name));
    }

    public function remove(): void
    {
        $this->recordEvent(new TagRemoved((string) $this->id, $this->slug->value()));
    }

    private static function assertName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        if ($name === '' || mb_strlen($name) > self::NAME_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A tag name is between 1 and %d characters.', self::NAME_MAX_LENGTH));
        }

        return $name;
    }
}
