<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddBlogBundle\Application\Query\FindPosts\FindPostsQuery;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResourceFactory;
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
final readonly class PostGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private PostResourceFactory $resources,
    ) {}

    /**
     * @return PagerfantaInterface<PostResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var array<string, mixed> $criteria */
        $criteria = $parameters->get('criteria', []);
        /** @var array<string, string> $sorting */
        $sorting = $parameters->get('sorting', $grid->getSorting());

        $models = $this->queryBus->ask(new FindPostsQuery(
            page: GridPageResolver::getCurrentPage($grid, $parameters),
            itemsPerPage: GridPageResolver::getItemsPerPage($grid, $parameters),
            criteria: $criteria,
            withSorting: $sorting,
        ));

        $data = [];
        foreach ($models as $model) {
            $data[] = $this->resources->forList($model);
        }

        return new Pagerfanta(new FixedAdapter($models->count(), $data));
    }
}
