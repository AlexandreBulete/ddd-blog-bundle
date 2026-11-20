<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates the blog tables.
 *
 * DBAL's built-in types only, never the bundle's own: a migration is a
 * snapshot, and must not change when a mapped type changes tomorrow. Each
 * built-in below produces the column the mapped type produces today — the
 * integration tests check it on PostgreSQL, MySQL and SQLite.
 *
 * Index names are left to DBAL, which derives them the way the ORM derives
 * them from the mapping.
 */
final class Version20261120100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Blog: posts, categories, tags and their indexes.';
    }

    public function up(Schema $schema): void
    {
        $post = $schema->createTable('blog_post');
        $post->addColumn('id', Types::GUID);
        $post->addColumn('title', Types::STRING, ['length' => 200]);
        $post->addColumn('slug', Types::STRING, ['length' => 190]);
        $post->addColumn('excerpt', Types::TEXT, ['notnull' => false]);
        $post->addColumn('body', Types::TEXT);
        $post->addColumn('author_id', Types::GUID, ['notnull' => false]);
        $post->addColumn('status', Types::STRING, ['length' => 20]);
        $post->addColumn('published_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $post->addColumn('featured', Types::BOOLEAN);
        $post->addColumn('category_ids', Types::JSONB);
        $post->addColumn('tag_ids', Types::JSONB);
        $post->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $post->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $post->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $post->addUniqueIndex(['slug']);
        $post->addIndex(['status', 'published_at']);

        $category = $schema->createTable('blog_category');
        $category->addColumn('id', Types::GUID);
        $category->addColumn('name', Types::STRING, ['length' => 100]);
        $category->addColumn('slug', Types::STRING, ['length' => 190]);
        $category->addColumn('description', Types::TEXT, ['notnull' => false]);
        $category->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $category->addColumn('updated_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $category->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $category->addUniqueIndex(['slug']);

        $tag = $schema->createTable('blog_tag');
        $tag->addColumn('id', Types::GUID);
        $tag->addColumn('name', Types::STRING, ['length' => 60]);
        $tag->addColumn('slug', Types::STRING, ['length' => 190]);
        $tag->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $tag->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $tag->addUniqueIndex(['slug']);

        foreach (['category', 'tag'] as $kind) {
            $index = $schema->createTable("blog_post_{$kind}");
            $index->addColumn('post_id', Types::GUID);
            $index->addColumn("{$kind}_id", Types::GUID);
            $index->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('post_id', "{$kind}_id")->create());
            $index->addIndex(["{$kind}_id"]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (['blog_post_tag', 'blog_post_category', 'blog_tag', 'blog_category', 'blog_post'] as $table) {
            $schema->dropTable($table);
        }
    }
}
