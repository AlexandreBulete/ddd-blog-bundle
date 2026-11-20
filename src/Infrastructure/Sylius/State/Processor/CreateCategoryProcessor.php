<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\CreateCategory\CreateCategoryCommand;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class CreateCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): CategoryResource
    {
        Assert::isInstanceOf($data, CategoryResource::class);
        Assert::stringNotEmpty($data->name);

        return CategoryResource::fromModel($this->commandBus->dispatch(new CreateCategoryCommand(
            name: $data->name,
            slug: $data->slug !== null && $data->slug !== '' ? Slug::fromString($data->slug) : null,
            description: $data->description,
        )));
    }
}
