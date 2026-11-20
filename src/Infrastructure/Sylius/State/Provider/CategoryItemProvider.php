<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Provider;

use AlexandreBulete\DddBlogBundle\Application\Query\FindCategory\FindCategoryQuery;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\RequestOption;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProviderInterface;

final readonly class CategoryItemProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    public function provide(Operation $operation, Context $context): ?CategoryResource
    {
        $id = $context->get(RequestOption::class)?->request()->attributes->getString('id');
        if ($id === null || $id === '') {
            return null;
        }

        return CategoryResource::fromModel(
            $this->queryBus->ask(new FindCategoryQuery(CategoryId::fromString($id))),
        );
    }
}
