<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * The picture heading a post. Nullable: posts written before keep none.
 */
final class Version20261123180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Blog: the visual of a post.';
    }

    public function up(Schema $schema): void
    {
        $post = $schema->getTable('blog_post');
        $post->addColumn('visual_id', Types::GUID, ['notnull' => false]);
        $post->addIndex(['visual_id']);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('blog_post')->dropColumn('visual_id');
    }
}
