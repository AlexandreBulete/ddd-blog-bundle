<?php

declare(strict_types=1);

use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\SlugGeneratorInterface;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\DoctrineCategoryRepository;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\DoctrinePostRepository;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\DoctrineTagRepository;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\ImmediateEventPublisher;
use AlexandreBulete\DddBlogBundle\Infrastructure\Text\AsciiSlugGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddBlogBundle\\', $src . '/')
        ->exclude([
            // Aggregates and value objects are never services.
            $src . '/Domain',
            $src . '/DddBlogBundle.php',
            // Doctrine instantiates types itself; index rows are never services.
            $src . '/Infrastructure/Doctrine/Type',
            $src . '/Infrastructure/Doctrine/Index',
            $src . '/Infrastructure/Doctrine/Mapping',
            $src . '/Infrastructure/Doctrine/IndexMapping',
            // Conditional: services_migrations.php, services_admin.php, services_media.php.
            $src . '/Infrastructure/Media',
            $src . '/Infrastructure/Doctrine/Migrations',
            $src . '/Infrastructure/Sylius',
            $src . '/Infrastructure/Symfony/Form',
        ]);

    // ── Ports → adapters ────────────────────────────────────────────────────
    // Rebind any of these to change behaviour without touching the bundle
    // (an outbox-backed publisher, a slugger that keeps Unicode…).
    $services->alias(PostRepositoryInterface::class, DoctrinePostRepository::class);
    $services->alias(CategoryRepositoryInterface::class, DoctrineCategoryRepository::class);
    $services->alias(TagRepositoryInterface::class, DoctrineTagRepository::class);
    $services->alias(DomainEventPublisherInterface::class, ImmediateEventPublisher::class);
    $services->alias(SlugGeneratorInterface::class, AsciiSlugGenerator::class);
};
