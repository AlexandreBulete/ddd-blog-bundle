<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddBlogBundle\Application\Query\FindPost\FindPostQuery;
use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResourceFactory;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class PostItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private PostResourceFactory $resources,
    ) {}

    public function provide(Operation $operation, Context $context): ?PostResource
    {
        $id = $context->get(RequestOption::class)?->request()->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        /** @var Post $post */
        $post = $this->queryBus->ask(new FindPostQuery(PostId::fromString($id)));

        return $this->resources->fromModel($post);
    }
}
