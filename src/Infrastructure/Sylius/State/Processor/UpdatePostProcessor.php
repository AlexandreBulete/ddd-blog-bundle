<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\CategorizePost\CategorizePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\ChangePostSlug\ChangePostSlugCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\RetitlePost\RetitlePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\RewritePost\RewritePostCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\TagPost\TagPostCommand;
use AlexandreBulete\DddBlogBundle\Application\Query\FindPost\FindPostQuery;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResourceFactory;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\PostStatusTransition;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * A form submits every field; only what the author changed becomes a command,
 * so the history says "retitled", not "everything updated".
 */
final readonly class UpdatePostProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private PostStatusTransition $transition,
        private PostResourceFactory $resources,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): PostResource
    {
        Assert::isInstanceOf($data, PostResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        Assert::stringNotEmpty($data->title);

        $id = PostId::fromUlid($data->id);
        /** @var Post $post */
        $post = $this->queryBus->ask(new FindPostQuery($id));
        $before = $this->resources->fromModel($post);

        if ($data->title !== $before->title) {
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new RetitlePostCommand($id, $data->title));
        }
        if ($data->slug !== null && $data->slug !== '' && $data->slug !== $before->slug) {
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new ChangePostSlugCommand($id, Slug::fromString($data->slug)));
        }
        if (($data->body ?? '') !== $before->body || $data->excerpt !== $before->excerpt) {
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new RewritePostCommand($id, $data->body ?? '', $data->excerpt));
        }
        if ($data->categoryIds !== $before->categoryIds) {
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new CategorizePostCommand($id, array_map(CategoryId::fromString(...), $data->categoryIds)));
        }
        if ($data->tagNames() !== $before->tagNames()) {
            /** @var Post $post */
            $post = $this->commandBus->dispatch(new TagPostCommand($id, $data->tagNames()));
        }
        $post = $this->transition->apply($post, $data);

        return $this->resources->fromModel($post);
    }
}
