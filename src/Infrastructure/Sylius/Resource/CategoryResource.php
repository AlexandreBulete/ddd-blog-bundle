<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Grid\CategoryGrid;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\CreateCategoryProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\DeleteCategoryProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor\UpdateCategoryProcessor;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Provider\CategoryItemProvider;
use AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Form\Type\CategoryType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;

#[AsResource(
    alias: 'blog.category',
    section: 'admin',
    formType: CategoryType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Create(processor: CreateCategoryProcessor::class),
        new Update(provider: CategoryItemProvider::class, processor: UpdateCategoryProcessor::class),
        new Delete(provider: CategoryItemProvider::class, processor: DeleteCategoryProcessor::class),
        new Index(grid: CategoryGrid::class),
    ],
)]
final class CategoryResource implements ResourceInterface
{
    public function __construct(
        public ?AbstractUid $id = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: Category::NAME_MAX_LENGTH)]
        public ?string $name = null,
        #[Assert\Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'blog.slug.format')]
        public ?string $slug = null,
        #[Assert\Length(max: Category::DESCRIPTION_MAX_LENGTH)]
        public ?string $description = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id->value(),
            name: $category->name,
            slug: $category->slug->value(),
            description: $category->description,
        );
    }
}
