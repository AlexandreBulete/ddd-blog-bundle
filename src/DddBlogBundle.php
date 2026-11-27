<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle;

use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\AuthorIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\CategoryIdListType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\CategoryIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\PostIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\SlugType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\TagIdListType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\TagIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\VisualIdType;
use AlexandreBulete\DddMediaBundle\DddMediaBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Blogging as a drop-in Bounded Context: posts written in Markdown, filed
 * under categories and tags, published now or at a date ahead.
 *
 * It ships the use cases, their persistence, their schema and a back office.
 * The public side (templates, routes, URLs) belongs to the application: it
 * asks the public queries (FindPublishedPosts, FindPublishedPost…) and prints
 * the body with the `blog_markdown` Twig filter.
 *
 * @phpstan-type BlogConfig array{admin: array{enabled: bool, grid_limits: list<int>}, public_routes: array{post: ?string, category: ?string}}
 */
final class DddBlogBundle extends AbstractBundle
{
    protected string $extensionAlias = 'blog';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Sylius back-office screens. Turn off for a headless deployment.')
                        ->end()
                        ->arrayNode('grid_limits')
                            ->integerPrototype()->end()
                            ->defaultValue([20, 50, 100])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('public_routes')
                    ->info('The routes of the public pages, taking a "slug": the back office links to them.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('post')->defaultNull()->end()
                        ->scalarNode('category')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var BlogConfig $config */
        $container->parameters()->set('blog.admin.grid_limits', $config['admin']['grid_limits']);
        $container->parameters()->set('blog.public_routes', $config['public_routes']);

        $container->import($this->getPath() . '/config/services.php');
        if ($config['admin']['enabled']) {
            $container->import($this->getPath() . '/config/services_admin.php');
        }
        if (self::migrationsEnabled($builder)) {
            $container->import($this->getPath() . '/config/services_migrations.php');
        }
        if (self::mediaEnabled($builder)) {
            $container->import($this->getPath() . '/config/services_media.php');
        }
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Before the config tree is processed: defaults are not applied yet.
        $adminEnabled = true;
        /** @var list<array<string, mixed>> $configs */
        $configs = $builder->getExtensionConfig($this->extensionAlias);
        foreach ($configs as $config) {
            $admin = $config['admin'] ?? null;
            if (is_array($admin) && is_bool($admin['enabled'] ?? null)) {
                $adminEnabled = $admin['enabled'];
            }
        }

        $builder->prependExtensionConfig('doctrine', [
            'dbal' => [
                'types' => [
                    PostIdType::NAME => PostIdType::class,
                    CategoryIdType::NAME => CategoryIdType::class,
                    TagIdType::NAME => TagIdType::class,
                    AuthorIdType::NAME => AuthorIdType::class,
                    VisualIdType::NAME => VisualIdType::class,
                    SlugType::NAME => SlugType::class,
                    CategoryIdListType::NAME => CategoryIdListType::class,
                    TagIdListType::NAME => TagIdListType::class,
                ],
            ],
            'orm' => [
                'mappings' => [
                    'Blog' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/Mapping',
                        'prefix' => 'AlexandreBulete\DddBlogBundle\Domain\Model',
                        'alias' => 'Blog',
                    ],
                    'BlogIndex' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/IndexMapping',
                        'prefix' => 'AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index',
                        'alias' => 'BlogIndex',
                    ],
                ],
            ],
        ]);

        if (self::migrationsEnabled($builder)) {
            $builder->prependExtensionConfig('doctrine_migrations', ['enable_service_migrations' => true]);
        }

        $builder->prependExtensionConfig('framework', [
            'translator' => ['paths' => [$this->getPath() . '/translations']],
        ]);

        if ($adminEnabled) {
            // DddSyliusBundle only globs the application's own resources.
            $builder->prependExtensionConfig('sylius_resource', [
                'mapping' => ['paths' => [$this->getPath() . '/src/Infrastructure/Sylius/Resource']],
            ]);
        }
    }

    /**
     * Post visuals need alexandrebulete/ddd-media-bundle, an optional
     * dependency.
     */
    private static function mediaEnabled(ContainerBuilder $builder): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array(DddMediaBundle::class, $bundles, true);
    }

    /**
     * From `kernel.bundles`: inside loadExtension() the builder is scoped to
     * this extension, so hasExtension() would answer false.
     */
    private static function migrationsEnabled(ContainerBuilder $builder): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array(DoctrineMigrationsBundle::class, $bundles, true);
    }
}
