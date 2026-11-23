<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Media;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\VisualId;
use AlexandreBulete\DddMediaBundle\Domain\Service\MediaUsageInterface;
use AlexandreBulete\DddMediaBundle\Domain\ValueObject\MediaId;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Tells the media library which posts a picture heads, so that it is not
 * deleted from under them.
 */
final readonly class PostVisualUsage implements MediaUsageInterface
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private TranslatorInterface $translator,
    ) {}

    public function usagesOf(MediaId $id): array
    {
        return array_map(
            fn (Post $post): string => $this->translator->trans('blog.post.visual_usage', ['%title%' => $post->title]),
            $this->posts->withVisual(VisualId::fromString((string) $id)),
        );
    }
}
