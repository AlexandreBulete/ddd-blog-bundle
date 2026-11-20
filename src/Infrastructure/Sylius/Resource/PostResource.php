<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddBlogBundle\Domain\Model\Post;
use AlexandreBulete\DddBlogBundle\Domain\Model\PostStatus;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid\PostGrid;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\CreatePostProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\DeletePostProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\UpdatePostProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Provider\PostItemProvider;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Form\Type\PostType;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Validator\UniqueSlug;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Back-office form of a post: flat fields in, one command per field the
 * author changed out (UpdatePostProcessor).
 *
 * `status` and `publishAt` together say when the public reads it: draft, now,
 * at a date ahead (scheduled), or archived.
 */
#[AsResource(
    alias: 'blog.post',
    section: 'admin',
    formType: PostType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Create(processor: CreatePostProcessor::class),
        new Update(provider: PostItemProvider::class, processor: UpdatePostProcessor::class),
        new Delete(provider: PostItemProvider::class, processor: DeletePostProcessor::class),
        new Index(grid: PostGrid::class),
    ],
)]
#[UniqueSlug('post')]
final class PostResource implements ResourceInterface
{
    /**
     * @param list<string> $categoryIds
     */
    public function __construct(
        public ?AbstractUid $id = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: Post::TITLE_MAX_LENGTH)]
        public ?string $title = null,
        // Derived from the title when left blank on creation.
        #[Assert\Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'blog.slug.format')]
        public ?string $slug = null,
        #[Assert\Length(max: Post::EXCERPT_MAX_LENGTH)]
        public ?string $excerpt = null,
        public ?string $body = null,
        public array $categoryIds = [],
        // Comma-separated, as typed: unknown tags are created.
        public ?string $tags = null,
        public PostStatus $status = PostStatus::Draft,
        public ?\DateTimeImmutable $publishAt = null,
        public bool $featured = false,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?string $statusLabel = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    /**
     * @param list<string> $tagNames the post's tags, in its order
     */
    public static function fromModel(Post $post, array $tagNames, \DateTimeImmutable $now): self
    {
        return new self(
            id: $post->id->value(),
            title: $post->title,
            slug: $post->slug->value(),
            excerpt: $post->excerpt,
            body: $post->body,
            categoryIds: array_map(static fn ($id): string => (string) $id, $post->categoryIds),
            tags: implode(', ', $tagNames),
            status: $post->status,
            publishAt: $post->publishedAt,
            featured: $post->featured,
            updatedAt: $post->updatedAt ?? $post->createdAt,
            statusLabel: 'blog.post.status_' . ($post->isScheduledAt($now) ? 'scheduled' : $post->status->value),
        );
    }

    /**
     * @return list<string>
     */
    public function tagNames(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->tags ?? '')), static fn (string $name): bool => $name !== ''));
    }
}
