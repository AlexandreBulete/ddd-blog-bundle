<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class CategoryGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    /**
     * @param list<int> $limits
     */
    public function __construct(
        private readonly array $limits,
    ) {}

    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(CategoryGridProvider::class)
            ->setLimits($this->limits)
            ->orderBy('name', 'asc')
            ->addFilter(StringFilter::create('name', ['name']))
            ->addField(StringField::create('name')->setLabel('blog.category.name')->setSortable(true))
            ->addField(StringField::create('slug')->setLabel('blog.slug.label')->setSortable(true))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return CategoryResource::class;
    }
}
