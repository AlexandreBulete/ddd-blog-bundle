<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Model;

use AlexandreBulete\DddBlogBundle\Domain\Event\PostArchived;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostCategorized;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostDeleted;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostDrafted;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostFeatured;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostPublished;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostRetitled;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostRewritten;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostSlugChanged;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostTagged;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostUnfeatured;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostUnpublished;
use AlexandreBulete\DddBlogBundle\Domain\Exception\ArchivedPostException;
use AlexandreBulete\DddBlogBundle\Domain\Exception\EmptyPostException;
use AlexandreBulete\DddBlogBundle\Domain\Exception\PublishedPostException;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\AuthorId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddFoundation\Domain\Model\RecordsEvents;

/**
 * A blog post: a title, a Markdown body, and when the public may read it.
 *
 * Visibility is a date, not a job: a post published with a date ahead is
 * scheduled, and becomes visible when that date passes — nothing has to run
 * at that moment. Every read of the public side asks "visible at now?".
 *
 * Categories and tags are other aggregates, referenced by identifier only: a
 * post can be filed under several categories, which the blog never loads to
 * change the post.
 */
final class Post
{
    use RecordsEvents;

    public const int TITLE_MAX_LENGTH = 200;
    public const int EXCERPT_MAX_LENGTH = 500;
    public const int DEFAULT_WORDS_PER_MINUTE = 230;

    /**
     * @param list<CategoryId> $categoryIds
     * @param list<TagId>      $tagIds
     */
    private function __construct(
        private(set) PostId $id,
        private(set) string $title,
        private(set) Slug $slug,
        private(set) ?string $excerpt,
        private(set) string $body,
        private(set) ?AuthorId $authorId,
        private(set) PostStatus $status,
        private(set) ?\DateTimeImmutable $publishedAt,
        private(set) bool $featured,
        private(set) array $categoryIds,
        private(set) array $tagIds,
        private(set) \DateTimeImmutable $createdAt,
        private(set) ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function draft(
        PostId $id,
        string $title,
        Slug $slug,
        string $body,
        ?string $excerpt,
        ?AuthorId $authorId,
        \DateTimeImmutable $at,
    ): self {
        $post = new self(
            $id,
            self::assertTitle($title),
            $slug,
            self::normalizeExcerpt($excerpt),
            $body,
            $authorId,
            PostStatus::Draft,
            null,
            false,
            [],
            [],
            $at,
            null,
        );
        $post->recordEvent(new PostDrafted((string) $id, $post->title, $slug->value()));

        return $post;
    }

    public function retitle(string $title, \DateTimeImmutable $at): void
    {
        $title = self::assertTitle($title);
        if ($title === $this->title) {
            return;
        }

        $this->title = $title;
        $this->touch($at);
        $this->recordEvent(new PostRetitled((string) $this->id, $title));
    }

    /**
     * The use case checks that no other post holds the slug: the post cannot
     * see the others.
     */
    public function changeSlug(Slug $slug, \DateTimeImmutable $at): void
    {
        if ($slug->equals($this->slug)) {
            return;
        }

        $previous = $this->slug;
        $this->slug = $slug;
        $this->touch($at);
        $this->recordEvent(new PostSlugChanged((string) $this->id, $slug->value(), $previous->value()));
    }

    public function rewrite(string $body, ?string $excerpt, \DateTimeImmutable $at): void
    {
        $excerpt = self::normalizeExcerpt($excerpt);
        if ($body === $this->body && $excerpt === $this->excerpt) {
            return;
        }
        if ($this->status === PostStatus::Published && trim($body) === '') {
            throw new EmptyPostException('A published post cannot be emptied: unpublish it first.');
        }

        $this->body = $body;
        $this->excerpt = $excerpt;
        $this->touch($at);
        $this->recordEvent(new PostRewritten((string) $this->id));
    }

    /**
     * Replaces the whole set, in the order given (the first one is the main
     * category, for a theme that shows only one).
     *
     * @param list<CategoryId> $categoryIds
     */
    public function categorize(array $categoryIds, \DateTimeImmutable $at): void
    {
        $categoryIds = self::unique($categoryIds);
        if (self::sameIds($categoryIds, $this->categoryIds)) {
            return;
        }

        $this->categoryIds = $categoryIds;
        $this->touch($at);
        $this->recordEvent(new PostCategorized((string) $this->id, self::strings($categoryIds)));
    }

    /**
     * @param list<TagId> $tagIds
     */
    public function tag(array $tagIds, \DateTimeImmutable $at): void
    {
        $tagIds = self::unique($tagIds);
        if (self::sameIds($tagIds, $this->tagIds)) {
            return;
        }

        $this->tagIds = $tagIds;
        $this->touch($at);
        $this->recordEvent(new PostTagged((string) $this->id, self::strings($tagIds)));
    }

    /**
     * Publishes now, or at a date ahead (scheduled). On a post already
     * published, moves its publication date: rescheduling is publishing again.
     */
    public function publish(?\DateTimeImmutable $publishAt, \DateTimeImmutable $now): void
    {
        if (trim($this->body) === '') {
            throw new EmptyPostException();
        }

        $publishedAt = $publishAt ?? $now;
        if ($this->status === PostStatus::Published && self::sameInstant($this->publishedAt, $publishedAt)) {
            return;
        }

        $this->status = PostStatus::Published;
        $this->publishedAt = $publishedAt;
        $this->touch($now);
        $this->recordEvent(new PostPublished((string) $this->id, $publishedAt));
    }

    /**
     * Back to draft: hidden from the public, and its publication date
     * forgotten — publishing it again sets a new one.
     */
    public function unpublish(\DateTimeImmutable $at): void
    {
        if ($this->status !== PostStatus::Published) {
            return;
        }

        $this->status = PostStatus::Draft;
        $this->publishedAt = null;
        $this->touch($at);
        $this->recordEvent(new PostUnpublished((string) $this->id));
    }

    /**
     * Hidden from the public, kept for the record. Publishing it again is
     * allowed: archiving is not deleting.
     */
    public function archive(\DateTimeImmutable $at): void
    {
        if ($this->status === PostStatus::Archived) {
            return;
        }

        $this->status = PostStatus::Archived;
        $this->featured = false;
        $this->touch($at);
        $this->recordEvent(new PostArchived((string) $this->id));
    }

    /**
     * Highlighted by the public side ("à la une"). Any status: a draft can be
     * prepared as the next featured post, it only shows once visible.
     */
    public function feature(\DateTimeImmutable $at): void
    {
        if ($this->featured) {
            return;
        }
        if ($this->status === PostStatus::Archived) {
            throw new ArchivedPostException();
        }

        $this->featured = true;
        $this->touch($at);
        $this->recordEvent(new PostFeatured((string) $this->id));
    }

    public function unfeature(\DateTimeImmutable $at): void
    {
        if (!$this->featured) {
            return;
        }

        $this->featured = false;
        $this->touch($at);
        $this->recordEvent(new PostUnfeatured((string) $this->id));
    }

    /**
     * Records the deletion; the repository deletes. A post the public can
     * read, or will read at a scheduled date, is unpublished or archived
     * first: a link out there must not break by accident.
     */
    public function delete(): void
    {
        if ($this->status === PostStatus::Published) {
            throw new PublishedPostException();
        }

        $this->recordEvent(new PostDeleted((string) $this->id, $this->slug->value()));
    }

    public function isVisibleAt(\DateTimeImmutable $now): bool
    {
        return $this->status === PostStatus::Published
            && $this->publishedAt !== null
            && $this->publishedAt <= $now;
    }

    public function isScheduledAt(\DateTimeImmutable $now): bool
    {
        return $this->status === PostStatus::Published
            && $this->publishedAt !== null
            && $this->publishedAt > $now;
    }

    public function isFiledUnder(CategoryId $categoryId): bool
    {
        foreach ($this->categoryIds as $id) {
            if ($id->equals($categoryId)) {
                return true;
            }
        }

        return false;
    }

    public function isTaggedWith(TagId $tagId): bool
    {
        foreach ($this->tagIds as $id) {
            if ($id->equals($tagId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Words of the body as a reader sees them: Markdown syntax, link targets
     * and HTML tags left out.
     */
    public function wordCount(): int
    {
        $text = preg_replace(
            ['/```.*?```/s', '/`[^`]*`/', '/!\[[^\]]*\]\([^)]*\)/', '/\]\([^)]*\)/', '/<[^>]+>/'],
            [' ', ' ', ' ', ']', ' '],
            $this->body,
        ) ?? $this->body;

        return (int) preg_match_all("/[\\p{L}\\p{N}]+(?:['’-][\\p{L}\\p{N}]+)*/u", $text);
    }

    /**
     * Minutes, rounded up, at least one for a post that has any word.
     *
     * @param positive-int $wordsPerMinute
     */
    public function readingTime(int $wordsPerMinute = self::DEFAULT_WORDS_PER_MINUTE): int
    {
        $words = $this->wordCount();

        return $words === 0 ? 0 : (int) ceil($words / $wordsPerMinute);
    }

    /**
     * Same instant, to the microsecond: two DateTimeImmutable are never ===.
     */
    public static function sameInstant(?\DateTimeImmutable $a, ?\DateTimeImmutable $b): bool
    {
        return $a?->format('U.u') === $b?->format('U.u');
    }

    private function touch(\DateTimeImmutable $at): void
    {
        $this->updatedAt = $at;
    }

    private static function assertTitle(string $title): string
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > self::TITLE_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A post title is between 1 and %d characters.', self::TITLE_MAX_LENGTH));
        }

        return $title;
    }

    private static function normalizeExcerpt(?string $excerpt): ?string
    {
        $excerpt = $excerpt === null ? null : trim($excerpt);
        if ($excerpt === null || $excerpt === '') {
            return null;
        }
        if (mb_strlen($excerpt) > self::EXCERPT_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A post excerpt is %d characters at most.', self::EXCERPT_MAX_LENGTH));
        }

        return $excerpt;
    }

    /**
     * @template T of CategoryId|TagId
     *
     * @param list<T> $ids
     *
     * @return list<T>
     */
    private static function unique(array $ids): array
    {
        $unique = [];
        foreach ($ids as $id) {
            $unique[(string) $id] ??= $id;
        }

        return array_values($unique);
    }

    /**
     * @param list<CategoryId|TagId> $a
     * @param list<CategoryId|TagId> $b
     */
    private static function sameIds(array $a, array $b): bool
    {
        return self::strings($a) === self::strings($b);
    }

    /**
     * @param list<CategoryId|TagId> $ids
     *
     * @return list<string>
     */
    private static function strings(array $ids): array
    {
        return array_map(static fn (CategoryId|TagId $id): string => (string) $id, $ids);
    }
}
