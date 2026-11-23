<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\DraftPost\DraftPostCommand;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Media\PostVisuals;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResourceFactory;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\PostStatusTransition;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

/**
 * Drafts, then applies the status the form asked for (publish now, schedule,
 * feature): one command per intention, in the order a person would.
 */
final readonly class CreatePostProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private PostStatusTransition $transition,
        private PostResourceFactory $resources,
        private ?PostVisuals $visuals = null,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): PostResource
    {
        Assert::isInstanceOf($data, PostResource::class);
        Assert::stringNotEmpty($data->title);

        /** @var Post $post */
        $post = $this->commandBus->dispatch(new DraftPostCommand(
            title: $data->title,
            body: $data->body ?? '',
            excerpt: $data->excerpt,
            slug: $data->slug !== null && $data->slug !== '' ? Slug::fromString($data->slug) : null,
            categoryIds: array_map(CategoryId::fromString(...), $data->categoryIds),
            tags: $data->tagNames(),
        ));
        $post = $this->transition->apply($post, $data);
        if ($this->visuals !== null) {
            $post = $this->visuals->change($post, $data->visualFile, $data->removeVisual);
        }

        return $this->resources->fromModel($post);
    }
}
