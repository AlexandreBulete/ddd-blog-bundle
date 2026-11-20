<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\ArchivePost\ArchivePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\FeaturePost\FeaturePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\PublishPost\PublishPostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\UnfeaturePost\UnfeaturePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\UnpublishPost\UnpublishPostCommand;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Model\PostStatus;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;

/**
 * Turns the status, publication date and "featured" box of the form into the
 * commands that reach them from where the post is.
 */
final readonly class PostStatusTransition
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function apply(Post $post, PostResource $data): Post
    {
        $id = $post->id;

        // Unfeature before archiving and feature after publishing: an archived
        // post cannot be featured, and the order a person would click in.
        if (!$data->featured && $post->featured) {
            $post = $this->dispatch(new UnfeaturePostCommand($id));
        }

        $post = match ($data->status) {
            PostStatus::Published => $post->status === PostStatus::Published && ($data->publishAt === null || Post::sameInstant($data->publishAt, $post->publishedAt))
                ? $post
                : $this->dispatch(new PublishPostCommand($id, $data->publishAt)),
            PostStatus::Draft => $post->status === PostStatus::Published ? $this->dispatch(new UnpublishPostCommand($id)) : $post,
            PostStatus::Archived => $post->status === PostStatus::Archived ? $post : $this->dispatch(new ArchivePostCommand($id)),
        };

        if ($data->featured && !$post->featured) {
            $post = $this->dispatch(new FeaturePostCommand($id));
        }

        return $post;
    }

    /**
     * @param PublishPostCommand|UnpublishPostCommand|ArchivePostCommand|FeaturePostCommand|UnfeaturePostCommand $command
     */
    private function dispatch(object $command): Post
    {
        return $this->commandBus->dispatch($command);
    }
}
