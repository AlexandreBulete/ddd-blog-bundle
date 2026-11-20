<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Twig;

use AlexandreBulete\DddBlogBundle\Infrastructure\Markdown\MarkdownRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * `{{ post.body|blog_markdown }}` prints the body as HTML (safe: raw HTML in
 * the Markdown is escaped); `blog_toc(post.body)` lists its sections.
 */
final class BlogExtension extends AbstractExtension
{
    public function __construct(private readonly MarkdownRenderer $markdown) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('blog_markdown', $this->markdown->toHtml(...), ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('blog_toc', $this->markdown->tableOfContents(...)),
        ];
    }
}
