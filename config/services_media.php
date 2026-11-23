<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Loaded only when alexandrebulete/ddd-media-bundle is registered: post
 * visuals, and the guard that keeps the library from deleting one in use.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddBlogBundle\\Infrastructure\\Media\\', $src . '/Infrastructure/Media/');
};
