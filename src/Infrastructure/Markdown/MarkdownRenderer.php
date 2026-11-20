<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Node\StringContainerHelper;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

/**
 * Post bodies to HTML, and the table of contents of their sections.
 *
 * The author writes Markdown, not HTML: raw HTML is escaped and unsafe links
 * (javascript:, data:) are dropped, so the output can be printed as is.
 * Headings get a stable id, which the table of contents links to.
 *
 * Rendered on read, every time: cheap at blog sizes, and a change of renderer
 * never leaves stale HTML in the database. Put an HTTP cache in front when
 * that stops being true.
 */
final class MarkdownRenderer
{
    private const string ANCHOR_PREFIX = 'section';

    private readonly MarkdownParser $parser;
    private readonly HtmlRenderer $renderer;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 50,
            'heading_permalink' => [
                'id_prefix' => self::ANCHOR_PREFIX,
                'fragment_prefix' => self::ANCHOR_PREFIX,
                'insert' => 'none',
                'apply_id_to_heading' => true,
                'min_heading_level' => 2,
                'max_heading_level' => 3,
            ],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());

        $this->parser = new MarkdownParser($environment);
        $this->renderer = new HtmlRenderer($environment);
    }

    public function toHtml(string $markdown): string
    {
        return (string) $this->renderer->renderDocument($this->parser->parse($markdown));
    }

    /**
     * The h2 and h3 of a body, in order, with the anchor toHtml() gives them.
     *
     * @return list<array{level: int, title: string, anchor: string}>
     */
    public function tableOfContents(string $markdown): array
    {
        $document = $this->parser->parse($markdown);
        $entries = [];
        foreach ($document->iterator() as $node) {
            if (!$node instanceof Heading || $node->getLevel() < 2 || $node->getLevel() > 3) {
                continue;
            }

            // With `apply_id_to_heading`, the id the HTML carries is set on
            // the heading itself; with an inserted link, on the link node.
            $anchor = $node->data->get('attributes/id', null);
            foreach ($node->children() as $child) {
                if ($child instanceof HeadingPermalink) {
                    $anchor = self::ANCHOR_PREFIX . '-' . $child->getSlug();
                }
            }
            if (!is_string($anchor) || $anchor === '') {
                continue;
            }

            $entries[] = [
                'level' => $node->getLevel(),
                'title' => trim(StringContainerHelper::getChildText($node)),
                'anchor' => $anchor,
            ];
        }

        return $entries;
    }
}
