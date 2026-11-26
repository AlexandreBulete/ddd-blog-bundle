<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Search;

use function Symfony\Component\String\u;

/**
 * Text as the search index stores it, and a visitor's words as they are
 * looked up: lowercase ASCII, punctuation and Markdown marks turned to
 * spaces — "Émery" is found by "emery", "l'été" by "ete".
 */
final class SearchText
{
    /** Words looked up at most: the rest of a long query adds nothing. */
    private const int MAX_TERMS = 6;

    public static function normalize(string $text): string
    {
        $ascii = u($text)->ascii()->lower()->toString();

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $ascii));
    }

    /**
     * The words of a query, each to be found (in any order): two characters
     * at least, duplicates dropped.
     *
     * @return list<string>
     */
    public static function terms(string $query): array
    {
        $terms = array_filter(
            explode(' ', self::normalize($query)),
            static fn (string $term): bool => strlen($term) >= 2,
        );

        return array_slice(array_values(array_unique($terms)), 0, self::MAX_TERMS);
    }
}
