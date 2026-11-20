<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Admin\Menu;

use AlexandreBulete\DddSyliusBundle\Admin\Menu\MenuContributorInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * One "Blog" entry with its screens, each shown only to whom its list query
 * lets in (the permission of that query, ADR 0008 of the skeleton).
 */
#[AutoconfigureTag('app.menu_contributor', ['priority' => 30])]
final readonly class BlogMenuContributor implements MenuContributorInterface
{
    private const array SCREENS = [
        'blog_posts' => ['blog_admin_post_index', 'blog.post.index', 'blog.find_posts'],
        'blog_categories' => ['blog_admin_category_index', 'blog.category.index', 'blog.find_categories'],
        'blog_tags' => ['blog_admin_tag_index', 'blog.tag.index', 'blog.find_tags'],
    ];

    public function __construct(
        private AuthorizationCheckerInterface $authorization,
    ) {}

    public function contribute(ItemInterface $menu): void
    {
        $root = null;
        foreach (self::SCREENS as $name => [$route, $label, $permission]) {
            if (!$this->authorization->isGranted($permission)) {
                continue;
            }
            $root ??= $menu->getChild('blog') ?? $menu->addChild('blog')
                ->setLabel('blog.menu.root')
                ->setLabelAttribute('icon', 'tabler:article');
            $root->addChild($name, ['route' => $route])->setLabel($label);
        }
    }
}
