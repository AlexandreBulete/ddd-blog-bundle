<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddBlogBundle\Application\Command\DeletePost\DeletePostCommand;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\PostId;
use AlexandreBulete\DddBlogBundle\Infrastructure\Media\PostVisuals;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class DeletePostProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private ?PostVisuals $visuals = null,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        Assert::isInstanceOf($data, PostResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        $this->commandBus->dispatch(new DeletePostCommand(PostId::fromUlid($data->id)));
        // Its picture leaves with it, unless something else still shows it.
        if ($this->visuals !== null && $data->visualId !== null) {
            $this->visuals->release($data->visualId);
        }

        return null;
    }
}
