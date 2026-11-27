<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddBlogBundle\Infrastructure\Media\PostVisuals;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

/**
 * Every post, newest change first. Decorate it to add a column.
 */
class PostGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    /**
     * @param list<int> $limits
     */
    public function __construct(
        private readonly array $limits,
        private readonly ?PostVisuals $visuals = null,
    ) {}

    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        if ($this->visuals !== null) {
            $gridBuilder->addField(TwigField::create('visualUrl', '@DddMedia/admin/thumbnail.html.twig')->setLabel('blog.post.visual'));
        }
        $gridBuilder
            ->setProvider(PostGridProvider::class)
            ->setLimits($this->limits)
            ->orderBy('updatedAt', 'desc')
            ->addFilter(StringFilter::create('title', ['title']))
            ->addField(StringField::create('title')->setLabel('blog.post.title')->setSortable(true))
            ->addField(TwigField::create('status', '@DddSylius/admin/grid/field/status.html.twig')->setLabel('blog.post.status')->setPath('adminStatus'))
            ->addField(DateTimeField::create('publishAt')->setLabel('blog.post.published_at')->setPath('publishAt')->setSortable(true, 'publishedAt'))
            ->addField(TwigField::create('featured', '@SyliusBootstrapAdminUi/shared/grid/field/boolean.html.twig')->setLabel('blog.post.featured')->setSortable(true))
            ->addField(DateTimeField::create('updatedAt')->setLabel('sylius.ui.updated_at')->setSortable(true))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return PostResource::class;
    }
}
