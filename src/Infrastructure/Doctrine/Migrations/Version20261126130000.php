<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Migrations;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

use function Symfony\Component\String\u;

/**
 * The search index of the posts, filled with the posts already written.
 *
 * The normalization is copied here, not called: a migration is a snapshot,
 * and the repository rewrites a post's row on its next save anyway.
 */
final class Version20261126130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Blog: the search index of the posts.';
    }

    public function up(Schema $schema): void
    {
        $search = $schema->createTable('blog_post_search');
        $search->addColumn('post_id', Types::GUID);
        $search->addColumn('title', Types::STRING, ['length' => 255]);
        $search->addColumn('content', Types::TEXT);
        $search->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('post_id')->create());
    }

    public function postUp(Schema $schema): void
    {
        $posts = $this->connection->fetchAllAssociative('SELECT id, title, excerpt, body FROM blog_post');
        foreach ($posts as $post) {
            $text = static fn (string $column): string => is_string($post[$column]) ? $post[$column] : '';
            $this->connection->insert('blog_post_search', [
                'post_id' => $post['id'],
                'title' => mb_substr(self::normalize($text('title')), 0, 255),
                'content' => self::normalize($text('excerpt') . ' ' . $text('body')),
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('blog_post_search');
    }

    private static function normalize(string $text): string
    {
        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', u($text)->ascii()->lower()->toString()));
    }
}
