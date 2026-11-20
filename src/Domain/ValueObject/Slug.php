<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\ValueObject;

use AlexandreBulete\DddFoundation\Domain\ValueObject\StringVO;

/**
 * The URL segment of a post, a category or a tag: lowercase ASCII letters and
 * digits, separated by single hyphens.
 *
 * Only the format is checked here. Turning a title into a slug (accents,
 * punctuation) is a SlugGeneratorInterface's job; uniqueness, a use case's.
 */
final readonly class Slug extends StringVO
{
    public const int MAX_LENGTH = 190;

    protected function normalize(string $value): string
    {
        return strtolower(trim($value));
    }

    protected function validate(string $value): void
    {
        if (strlen($value) > self::MAX_LENGTH || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid slug "%s": lowercase letters and digits separated by hyphens, %d characters at most.', $value, self::MAX_LENGTH));
        }
    }
}
