<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Service;

use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;

/**
 * Turns a human text (a title, a tag name) into a slug: transliterated,
 * lowercased, punctuation dropped. Not unique by itself.
 */
interface SlugGeneratorInterface
{
    public function slugify(string $text): Slug;
}
