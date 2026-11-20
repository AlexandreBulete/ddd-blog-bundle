<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid\TagGrid;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\DeleteTagProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\UpdateTagProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Provider\TagItemProvider;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Form\Type\TagType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * No creation screen: a tag is created by tagging a post with it.
 */
#[AsResource(
    alias: 'blog.tag',
    section: 'admin',
    formType: TagType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Update(provider: TagItemProvider::class, processor: UpdateTagProcessor::class),
        new Delete(provider: TagItemProvider::class, processor: DeleteTagProcessor::class),
        new Index(grid: TagGrid::class),
    ],
)]
final class TagResource implements ResourceInterface
{
    public function __construct(
        public ?AbstractUid $id = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: Tag::NAME_MAX_LENGTH)]
        public ?string $name = null,
        public ?string $slug = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public static function fromModel(Tag $tag): self
    {
        return new self(id: $tag->id->value(), name: $tag->name, slug: $tag->slug->value());
    }
}
