<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\RemoveCategory\RemoveCategoryCommand;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\CategoryId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class DeleteCategoryProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        Assert::isInstanceOf($data, CategoryResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        $this->commandBus->dispatch(new RemoveCategoryCommand(CategoryId::fromUlid($data->id)));

        return null;
    }
}
