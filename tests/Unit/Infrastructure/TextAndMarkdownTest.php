<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Tests\Unit\Infrastructure;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Markdown\MarkdownRenderer;
use AlexandreBulete\DddBlogBundle\Infrastructure\Text\AsciiSlugGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextAndMarkdownTest extends TestCase
{
    #[Test]
    public function a_title_becomes_an_ascii_slug(): void
    {
        $slugger = new AsciiSlugGenerator();

        self::assertSame('l-ete-ou-j-ai-lu-zola', $slugger->slugify("  L'Été où j'ai lu Zola ! ")->value());
        self::assertLessThanOrEqual(Slug::MAX_LENGTH, strlen($slugger->slugify(str_repeat('mot ', 100))->value()));
    }

    #[Test]
    public function a_text_without_letters_has_no_slug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new AsciiSlugGenerator())->slugify('!!!');
    }

    #[Test]
    public function a_slug_rejects_what_is_not_lowercase_words_and_hyphens(): void
    {
        self::assertSame('abc-1', Slug::fromString(' ABC-1 ')->value());

        $this->expectException(\InvalidArgumentException::class);
        Slug::fromString('a--b');
    }

    #[Test]
    public function raw_html_and_unsafe_links_are_neutralised(): void
    {
        $html = (new MarkdownRenderer())->toHtml("<script>alert(1)</script>\n\n[x](javascript:alert(1))");

        self::assertStringNotContainsString('<script>', $html);
        self::assertStringNotContainsString('javascript:', $html);
    }

    #[Test]
    public function the_table_of_contents_links_to_the_rendered_headings(): void
    {
        $markdown = "# Title\n\n## Première partie\n\ntext\n\n### Un détail\n\n## Seconde *partie*\n";
        $renderer = new MarkdownRenderer();

        $toc = $renderer->tableOfContents($markdown);
        $html = $renderer->toHtml($markdown);

        self::assertSame([2, 3, 2], array_column($toc, 'level'));
        self::assertSame(['Première partie', 'Un détail', 'Seconde partie'], array_column($toc, 'title'));
        foreach ($toc as $entry) {
            self::assertStringContainsString(sprintf('id="%s"', $entry['anchor']), $html);
        }
    }
}
