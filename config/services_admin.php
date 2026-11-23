<?php

declare(strict_types=1);

use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid\CategoryGrid;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid\PostGrid;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid\TagGrid;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/**
 * Loaded only when `blog.admin.enabled` is true: everything Sylius, nothing
 * else. Resources are DTOs, not services.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddBlogBundle\\Infrastructure\\Sylius\\', $src . '/Infrastructure/Sylius/')
        ->exclude([$src . '/Infrastructure/Sylius/Resource/PostResource.php', $src . '/Infrastructure/Sylius/Resource/CategoryResource.php', $src . '/Infrastructure/Sylius/Resource/TagResource.php']);
    $services->load('AlexandreBulete\\DddBlogBundle\\Infrastructure\\Symfony\\Form\\', $src . '/Infrastructure/Symfony/Form/');

    foreach ([PostGrid::class, CategoryGrid::class, TagGrid::class] as $grid) {
        $services->set($grid)->arg('$limits', param('blog.admin.grid_limits'));
    }
};
