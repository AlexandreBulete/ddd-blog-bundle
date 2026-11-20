<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Tests\Integration;

use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The bundle's migration and its ORM mapping describe the same schema on the
 * platform under test: a migration written with DBAL built-in types stays
 * safe only as long as this holds.
 */
final class MigrationMatchesMappingTest extends TestCase
{
    #[Test]
    public function the_migrated_schema_is_the_mapped_schema(): void
    {
        $em = BlogDatabase::migrated();

        self::assertSame([], (new SchemaTool($em))->getUpdateSchemaSql($em->getMetadataFactory()->getAllMetadata()));
        self::assertTrue($em->getConnection()->createSchemaManager()->tablesExist(['blog_post', 'blog_category', 'blog_tag', 'blog_post_category', 'blog_post_tag']));

        $em->getConnection()->close();
    }
}
