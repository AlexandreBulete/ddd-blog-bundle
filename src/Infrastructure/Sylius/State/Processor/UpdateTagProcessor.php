<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\RenameTag\RenameTagCommand;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\TagResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class UpdateTagProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): TagResource
    {
        Assert::isInstanceOf($data, TagResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        Assert::stringNotEmpty($data->name);

        return TagResource::fromModel($this->commandBus->dispatch(new RenameTagCommand(TagId::fromUlid($data->id), $data->name)));
    }
}
