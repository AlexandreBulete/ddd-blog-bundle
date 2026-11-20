<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddBlogBundle\Application\Query\FindCategories\FindCategoriesQuery;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddSyliusBundle\Grid\GridPageResolver;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

/**
 * Feeds the grid through the query bus; pagination already happened in the
 * repository, hence FixedAdapter.
 */
final readonly class CategoryGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    /**
     * @return PagerfantaInterface<CategoryResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var array<string, mixed> $criteria */
        $criteria = $parameters->get('criteria', []);
        /** @var array<string, string> $sorting */
        $sorting = $parameters->get('sorting', $grid->getSorting());

        $models = $this->queryBus->ask(new FindCategoriesQuery(
            page: GridPageResolver::getCurrentPage($grid, $parameters),
            itemsPerPage: GridPageResolver::getItemsPerPage($grid, $parameters),
            criteria: $criteria,
            withSorting: $sorting,
        ));

        $data = [];
        foreach ($models as $model) {
            $data[] = CategoryResource::fromModel($model);
        }

        return new Pagerfanta(new FixedAdapter($models->count(), $data));
    }
}
