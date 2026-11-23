<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Media;

use AlexandreBulete\DddBlogBundle\Application\Command\ChangePostVisual\ChangePostVisualCommand;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\VisualId;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddMediaBundle\Infrastructure\Imagine\MediaUrls;
use AlexandreBulete\DddMediaBundle\Infrastructure\Sylius\Resource\MediaResourceFactory;
use AlexandreBulete\DddMediaBundle\Infrastructure\Symfony\AttachedMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The visual of a post, kept in alexandrebulete/ddd-media-bundle. Defined
 * only when that bundle is installed: without it, posts have no picture field
 * and everything else works the same.
 *
 * A picture sent from a post's form belongs to that post: replacing it,
 * taking it off or deleting the post takes it out of the library too, unless
 * something else still shows it.
 */
final readonly class PostVisuals
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private AttachedMedia $attached,
        private MediaUrls $urls,
        private TranslatorInterface $translator,
    ) {}

    /**
     * @param iterable<Post> $posts
     */
    public function preload(iterable $posts): void
    {
        $ids = [];
        foreach ($posts as $post) {
            $ids[] = $post->visual;
        }
        $this->urls->preload($ids);
    }

    public function thumbnailUrl(Post $post): ?string
    {
        return $this->urls->url($post->visual, MediaResourceFactory::THUMBNAIL_FILTER);
    }

    /**
     * A new picture replaces the current one; `$takeOff` removes it.
     */
    public function change(Post $post, ?UploadedFile $file, bool $takeOff): Post
    {
        $previous = $post->visual;

        if ($file !== null) {
            $media = $this->attached->attach($file, $this->translator->trans('blog.post.visual_alt', ['%title%' => $post->title]));
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new ChangePostVisualCommand($post->id, VisualId::fromString((string) $media)));
        } elseif ($takeOff && $previous !== null) {
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new ChangePostVisualCommand($post->id, null));
        }

        if ($previous !== null && ($post->visual === null || !$previous->equals($post->visual))) {
            $this->attached->release($previous);
        }

        return $post;
    }

    public function release(string $visual): void
    {
        $this->attached->release($visual);
    }
}
