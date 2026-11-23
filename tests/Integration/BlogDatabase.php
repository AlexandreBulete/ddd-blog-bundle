<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Tests\Integration;

use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Migrations\Version20261120100000;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Migrations\Version20261123180000;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\AuthorIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\CategoryIdListType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\CategoryIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\PostIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\SlugType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\TagIdListType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\TagIdType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type\VisualIdType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\ORMSetup;
use Psr\Log\NullLogger;

/**
 * A real database holding the blog tables, built by the bundle's migration.
 *
 * DDD_TEST_DATABASE_URL picks the platform (CI runs PostgreSQL, MySQL and
 * SQLite); SQLite in memory otherwise. Only `blog_*` tables are seen, so a
 * shared test database does not leak other packages' tables in.
 */
final class BlogDatabase
{
    public static function migrated(): EntityManagerInterface
    {
        self::registerTypes();

        $root = dirname(__DIR__, 2) . '/src/Infrastructure/Doctrine';
        $config = ORMSetup::createConfiguration(true);
        $config->setMetadataDriverImpl(new SimplifiedXmlDriver([
            $root . '/Mapping' => 'AlexandreBulete\DddBlogBundle\Domain\Model',
            $root . '/IndexMapping' => 'AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Index',
        ]));
        $config->enableNativeLazyObjects(true);
        $config->setSchemaAssetsFilter(static fn (string $asset): bool => str_starts_with($asset, 'blog_'));

        $connection = self::connect($config);
        self::dropTables($connection);
        self::runMigration($connection);

        return new EntityManager($connection, $config);
    }

    private static function connect(Configuration $config): Connection
    {
        $url = getenv('DDD_TEST_DATABASE_URL');
        $params = (new DsnParser([
            'pdo-sqlite' => 'pdo_sqlite',
            'postgresql' => 'pdo_pgsql',
            'mysql' => 'pdo_mysql',
        ]))->parse(is_string($url) && $url !== '' ? $url : 'pdo-sqlite:///:memory:');

        return DriverManager::getConnection($params, $config);
    }

    private static function dropTables(Connection $connection): void
    {
        $schemaManager = $connection->createSchemaManager();
        foreach ($schemaManager->listTableNames() as $table) {
            $schemaManager->dropTable($table);
        }
    }

    /**
     * What `doctrine:migrations:migrate` does for a Schema API migration:
     * diff the introspected schema against the one up() describes.
     */
    private static function runMigration(Connection $connection): void
    {
        foreach ([Version20261120100000::class, Version20261123180000::class] as $class) {
            $migration = new $class($connection, new NullLogger());
            $schemaManager = $connection->createSchemaManager();
            $from = $schemaManager->introspectSchema();
            $to = clone $from;
            $migration->up($to);

            foreach ($connection->getDatabasePlatform()->getAlterSchemaSQL($schemaManager->createComparator()->compareSchemas($from, $to)) as $sql) {
                $connection->executeStatement($sql);
            }
        }
    }

    private static function registerTypes(): void
    {
        foreach ([
            PostIdType::NAME => PostIdType::class,
            CategoryIdType::NAME => CategoryIdType::class,
            TagIdType::NAME => TagIdType::class,
            AuthorIdType::NAME => AuthorIdType::class,
            SlugType::NAME => SlugType::class,
            CategoryIdListType::NAME => CategoryIdListType::class,
            TagIdListType::NAME => TagIdListType::class,
            VisualIdType::NAME => VisualIdType::class,
        ] as $name => $class) {
            if (!Type::hasType($name)) {
                Type::addType($name, $class);
            }
        }
    }
}
