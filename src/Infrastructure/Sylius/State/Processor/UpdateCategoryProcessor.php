<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\ChangeCategorySlug\ChangeCategorySlugCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\DescribeCategory\DescribeCategoryCommand;
use AlexandreBulete\DddBlogBundle\Application\Command\RenameCategory\RenameCategoryCommand;
use AlexandreBulete\DddBlogBundle\Application\Query\FindCategory\FindCategoryQuery;
use AlexandreBulete\DddBlogBundle\Domain\Model\Category;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class UpdateCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): CategoryResource
    {
        Assert::isInstanceOf($data, CategoryResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        Assert::stringNotEmpty($data->name);

        $id = CategoryId::fromUlid($data->id);
        /** @var Category $category */
        $category = $this->queryBus->ask(new FindCategoryQuery($id));

        if ($data->name !== $category->name) {
            /** @var Category $category */
            $category = $this->commandBus->dispatch(new RenameCategoryCommand($id, $data->name));
        }
        if ($data->slug !== null && $data->slug !== '' && $data->slug !== $category->slug->value()) {
            /** @var Category $category */
            $category = $this->commandBus->dispatch(new ChangeCategorySlugCommand($id, Slug::fromString($data->slug)));
        }
        $description = $data->description === null || trim($data->description) === '' ? null : trim($data->description);
        if ($description !== $category->description) {
            /** @var Category $category */
            $category = $this->commandBus->dispatch(new DescribeCategoryCommand($id, $description));
        }

        return CategoryResource::fromModel($category);
    }
}
