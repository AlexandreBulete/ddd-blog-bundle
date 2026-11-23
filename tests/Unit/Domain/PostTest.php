<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Tests\Unit\Domain;

use AlexandreBulete\DddBlogBundle\Domain\Event\PostCategorized;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostDrafted;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostPublished;
use AlexandreBulete\DddBlogBundle\Domain\Event\PostVisualChanged;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Model\PostStatus;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\VisualId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PostTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-11-20 10:00:00');
    }

    #[Test]
    public function a_new_post_is_a_draft_nobody_reads(): void
    {
        $post = $this->draft();

        self::assertSame(PostStatus::Draft, $post->status);
        self::assertFalse($post->isVisibleAt($this->now));
        self::assertInstanceOf(PostDrafted::class, $post->releaseEvents()[0]);
    }

    #[Test]
    public function publishing_without_a_date_makes_it_visible_now(): void
    {
        $post = $this->draft();
        $post->publish(null, $this->now);

        self::assertTrue($post->isVisibleAt($this->now));
        self::assertEquals($this->now, $post->publishedAt);
    }

    #[Test]
    public function a_post_published_with_a_date_ahead_is_scheduled_until_then(): void
    {
        $post = $this->draft();
        $at = $this->now->modify('+2 days');
        $post->publish($at, $this->now);

        self::assertTrue($post->isScheduledAt($this->now));
        self::assertFalse($post->isVisibleAt($this->now));
        self::assertTrue($post->isVisibleAt($at));
        self::assertFalse($post->isScheduledAt($at));
    }

    #[Test]
    public function republishing_at_the_same_date_records_nothing(): void
    {
        $post = $this->draft();
        $post->publish(null, $this->now);
        $post->releaseEvents();

        $post->publish($this->now, $this->now->modify('+1 hour'));

        self::assertSame([], $post->releaseEvents());
    }

    #[Test]
    public function rescheduling_moves_the_date(): void
    {
        $post = $this->draft();
        $post->publish($this->now->modify('+1 day'), $this->now);
        $post->releaseEvents();

        $post->publish($this->now->modify('+3 days'), $this->now);

        self::assertEquals($this->now->modify('+3 days'), $post->publishedAt);
        self::assertInstanceOf(PostPublished::class, $post->releaseEvents()[0]);
    }

    #[Test]
    public function an_empty_post_cannot_be_published(): void
    {
        $post = Post::draft(PostId::generate(), 'Empty', Slug::fromString('empty'), '  ', null, null, $this->now);

        $this->expectException(\DomainException::class);
        $post->publish(null, $this->now);
    }

    #[Test]
    public function unpublishing_hides_it_and_forgets_its_date(): void
    {
        $post = $this->draft();
        $post->publish(null, $this->now);
        $post->unpublish($this->now);

        self::assertSame(PostStatus::Draft, $post->status);
        self::assertNull($post->publishedAt);
        self::assertFalse($post->isVisibleAt($this->now));
    }

    #[Test]
    public function a_published_post_cannot_be_deleted(): void
    {
        $post = $this->draft();
        $post->publish(null, $this->now);

        $this->expectException(\DomainException::class);
        $post->delete();
    }

    #[Test]
    public function archiving_hides_it_and_drops_the_highlight(): void
    {
        $post = $this->draft();
        $post->publish(null, $this->now);
        $post->feature($this->now);
        $post->archive($this->now);

        self::assertFalse($post->isVisibleAt($this->now));
        self::assertFalse($post->featured);

        $this->expectException(\DomainException::class);
        $post->feature($this->now);
    }

    #[Test]
    public function a_post_is_filed_under_several_categories_in_the_order_given(): void
    {
        $post = $this->draft();
        [$a, $b] = [CategoryId::generate(), CategoryId::generate()];
        $post->releaseEvents();

        $post->categorize([$b, $a, $b], $this->now);

        self::assertSame([(string) $b, (string) $a], array_map('strval', $post->categoryIds));
        self::assertTrue($post->isFiledUnder($a));
        $event = $post->releaseEvents()[0];
        self::assertInstanceOf(PostCategorized::class, $event);
        self::assertSame([(string) $b, (string) $a], $event->categoryIds);

        $post->categorize([$b, $a], $this->now);
        self::assertSame([], $post->releaseEvents(), 'same set, same order: nothing changed');
    }

    #[Test]
    public function reading_time_counts_the_words_a_reader_sees(): void
    {
        $body = "## Un titre\n\nL'été, j'ai lu [ce livre](https://example.test/a-very-long-url).\n\n```php\necho 'ignored code';\n```\n";
        $post = Post::draft(PostId::generate(), 'T', Slug::fromString('t'), $body, null, null, $this->now);

        // Un, titre, L'été, j'ai, lu, ce, livre
        self::assertSame(7, $post->wordCount());
        self::assertSame(1, $post->readingTime());
        self::assertSame(0, Post::draft(PostId::generate(), 'T', Slug::fromString('u'), '', null, null, $this->now)->readingTime());
    }

    #[Test]
    public function a_title_is_required(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Post::draft(PostId::generate(), '   ', Slug::fromString('x'), '', null, null, $this->now);
    }

    #[Test]
    public function a_visual_change_is_recorded_only_when_the_visual_changes(): void
    {
        $post = $this->draft();
        $post->releaseEvents();
        $visual = VisualId::generate();

        $post->changeVisual($visual, $this->now);
        $post->changeVisual(VisualId::fromString((string) $visual), $this->now);
        $post->changeVisual(null, $this->now);
        $post->changeVisual(null, $this->now);

        $events = $post->releaseEvents();
        self::assertCount(2, $events);
        self::assertInstanceOf(PostVisualChanged::class, $events[1]);
        self::assertSame((string) $visual, $events[1]->previousVisualId);
        self::assertNull($post->visual);
    }

    private function draft(): Post
    {
        return Post::draft(PostId::generate(), 'Hello', Slug::fromString('hello'), 'Some words.', null, null, $this->now);
    }
}
