<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddBlogBundle\Application\Query\FindTag\FindTagQuery;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\TagResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class TagItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function provide(Operation $operation, Context $context): ?TagResource
    {
        $id = $context->get(RequestOption::class)?->request()->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        return TagResource::fromModel(
            $this->queryBus->ask(new FindTagQuery(TagId::fromString($id))),
        );
    }
}
