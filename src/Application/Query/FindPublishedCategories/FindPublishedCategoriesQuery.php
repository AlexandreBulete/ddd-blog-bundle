<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Query\FindPublishedCategories;

use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The categories a reader can browse: those holding at least one post the
 * public may read now, with how many, by name. A category filled only with
 * drafts or scheduled posts stays hidden, so that no link leads to an empty
 * page.
 *
 * @implements QueryInterface<list<PublishedCategory>>
 */
#[PublicAccess]
final readonly class FindPublishedCategoriesQuery implements QueryInterface
{
}
