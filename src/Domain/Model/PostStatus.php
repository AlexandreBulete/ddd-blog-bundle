<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Domain\Model;

/**
 * Where a post is in its life. "Scheduled" is not a status: it is a published
 * post whose publication date is still ahead, see Post::isScheduledAt().
 */
enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
