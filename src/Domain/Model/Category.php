<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Model;

use AlexandreBulete\DddBlogBundle\Domain\Event\CategoryCreated;
use AlexandreBulete\DddBlogBundle\Domain\Event\CategoryDescribed;
use AlexandreBulete\DddBlogBundle\Domain\Event\CategoryRemoved;
use AlexandreBulete\DddBlogBundle\Domain\Event\CategoryRenamed;
use AlexandreBulete\DddBlogBundle\Domain\Event\CategorySlugChanged;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * A heading posts are filed under. Flat: no parent category — a hierarchy is
 * added the day a second project needs one, not before.
 */
final class Category
{
    use RecordsEvents;

    public const int NAME_MAX_LENGTH = 100;
    public const int DESCRIPTION_MAX_LENGTH = 1000;

    private function __construct(
        private(set) CategoryId $id,
        private(set) string $name,
        private(set) Slug $slug,
        private(set) ?string $description,
        private(set) \DateTimeImmutable $createdAt,
        private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(CategoryId $id, string $name, Slug $slug, ?string $description, \DateTimeImmutable $at): self
    {
        $category = new self($id, self::assertName($name), $slug, self::normalizeDescription($description), $at, null);
        $category->recordEvent(new CategoryCreated((string) $id, $category->name, $slug->value()));

        return $category;
    }

    public function rename(string $name, \DateTimeImmutable $at): void
    {
        $name = self::assertName($name);
        if ($name === $this->name) {
            return;
        }

        $this->name = $name;
        $this->updatedAt = $at;
        $this->recordEvent(new CategoryRenamed((string) $this->id, $name));
    }

    public function describe(?string $description, \DateTimeImmutable $at): void
    {
        $description = self::normalizeDescription($description);
        if ($description === $this->description) {
            return;
        }

        $this->description = $description;
        $this->updatedAt = $at;
        $this->recordEvent(new CategoryDescribed((string) $this->id, $description));
    }

    public function changeSlug(Slug $slug, \DateTimeImmutable $at): void
    {
        if ($slug->equals($this->slug)) {
            return;
        }

        $previous = $this->slug;
        $this->slug = $slug;
        $this->updatedAt = $at;
        $this->recordEvent(new CategorySlugChanged((string) $this->id, $slug->value(), $previous->value()));
    }

    /**
     * Records the removal; the repository deletes. Whether posts are still
     * filed under it is checked by the use case, which can see them.
     */
    public function remove(): void
    {
        $this->recordEvent(new CategoryRemoved((string) $this->id, $this->slug->value()));
    }

    private static function assertName(string $name): string
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > self::NAME_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A category name is between 1 and %d characters.', self::NAME_MAX_LENGTH));
        }

        return $name;
    }

    private static function normalizeDescription(?string $description): ?string
    {
        $description = $description === null ? null : trim($description);
        if ($description === null || $description === '') {
            return null;
        }
        if (mb_strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A category description is %d characters at most.', self::DESCRIPTION_MAX_LENGTH));
        }

        return $description;
    }
}
