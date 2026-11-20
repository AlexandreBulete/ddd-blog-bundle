<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * The slug a back-office form will end up with — typed, or derived from the
 * title or name — is not held by another post or category.
 *
 * The use case checks it again (SlugAlreadyUsedException); this is what turns
 * the common case into a form error instead of an error page.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class UniqueSlug extends Constraint
{
    public string $message = 'blog.slug.taken';

    /**
     * @param 'post'|'category' $kind
     */
    public function __construct(
        public string $kind = 'post',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
