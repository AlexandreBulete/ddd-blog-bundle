# ddd-blog-bundle

Blogging as a reusable DDD Bounded Context for Symfony: posts written in
Markdown, filed under categories and tags, published now or scheduled, with a
Sylius back office.

The bundle owns the use cases, their persistence, their schema and the back
office. The public side — routes, templates, URLs — belongs to the
application, which asks the public queries and prints the result.

## Installation

```bash
composer require alexandrebulete/ddd-blog-bundle
```

The Flex recipe registers the bundle. Its tables (`blog_*`) come with its own
migrations: `bin/console doctrine:migrations:migrate`, nothing to diff.

Requires ddd-foundation, ddd-symfony-bundle, ddd-doctrine-bundle and
ddd-sylius-bundle.

## Model

| Aggregate | Holds |
|---|---|
| `Post` | title, slug, excerpt, Markdown body, author id, status, publication date, featured, category ids, tag ids |
| `Category` | name, slug, description — flat, no hierarchy |
| `Tag` | name, slug — created the first time a post is tagged with it |

- **Several categories per post**, in the order given: the first is the main
  one, for a theme that shows a single category.
- **Scheduling is a date, not a job.** A post published with a date ahead is
  scheduled; it becomes visible when the date passes, with nothing to run.
  Public reads always ask "visible now?" (`Post::isVisibleAt()`).
- **Author** is an identifier from the application's identity context (an IAM
  user id), with no foreign key: the blog does not know who users are.
- A published post cannot be emptied or deleted (unpublish or archive first);
  a category or tag still carried by posts cannot be removed.

## Use cases

Commands: `DraftPost`, `RetitlePost`, `ChangePostSlug`, `RewritePost`,
`CategorizePost`, `TagPost`, `PublishPost` (now or `publishAt`),
`UnpublishPost`, `ArchivePost`, `FeaturePost`, `UnfeaturePost`, `DeletePost`;
`CreateCategory`, `RenameCategory`, `DescribeCategory`, `ChangeCategorySlug`,
`RemoveCategory`; `RenameTag`, `RemoveTag`.

Back-office queries: `FindPosts`, `FindPost`, `FindCategories`,
`FindCategory`, `FindTags`, `FindTag`.

Public queries (`#[PublicAccess]`, open to any visitor, signed in or not):

| Query | For |
|---|---|
| `FindPublishedPosts` | a listing: all, by category, by tag, featured — paginated, most recent first |
| `FindPublishedPost` | one post by slug; a draft or a scheduled post is *not found* |
| `FindRelatedPosts` | "read also": sharing the most categories and tags, topped up with the latest |
| `FindPublishedCategories` | the categories holding visible posts, with their count, by name: a category menu that never leads to an empty page |
| `FindCategoryBySlug`, `FindTagBySlug` | resolve a URL segment (404 when unknown) |
| `FindCategoriesByIds`, `FindTagsByIds` | the categories and tags of a post, in its order |

Every other use case is a permission (`blog.draft_post`, `blog.find_posts`…),
granted through roles by an IAM.

## Rendering

```twig
{{ post.body|blog_markdown }}

{% for entry in blog_toc(post.body) %}
    <a href="#{{ entry.anchor }}" class="toc-{{ entry.level }}">{{ entry.title }}</a>
{% endfor %}

{{ post.readingTime }} min
```

`blog_markdown` is CommonMark + GitHub Flavored Markdown. Raw HTML is escaped
and unsafe links dropped: an author writes Markdown, not HTML, and the output
prints as is. `h2`/`h3` get ids, which `blog_toc()` links to.

## Configuration

```yaml
blog:
    admin:
        enabled: true          # Sylius screens; false for a headless setup
        grid_limits: [20, 50, 100]
```

## Seams

Ports bound in `config/services.php`, to rebind in the application:

| Port | Default | Rebind for |
|---|---|---|
| `DomainEventPublisherInterface` | `ImmediateEventPublisher` (in-process, in the transaction) | an outbox, for delivery outside the process |
| `SlugGeneratorInterface` | `AsciiSlugGenerator` (Symfony slugger) | slugs keeping Unicode |
| `PostRepositoryInterface`… | Doctrine | another storage |

## Persistence

A post keeps its category and tag ids in its own row (JSON), read and written
whole with it. Two index tables, `blog_post_category` and `blog_post_tag`,
are rewritten from it on every save, in the same transaction: filtering
by category or tag is an indexed `EXISTS`, portable to PostgreSQL, MySQL and
SQLite.

## Development

```bash
composer qa    # phpstan (max, strict) + deptrac + phpunit
```

Integration tests run on SQLite in memory, or on the database given by
`DDD_TEST_DATABASE_URL` (CI: PostgreSQL, MySQL, SQLite).

## License

MIT
