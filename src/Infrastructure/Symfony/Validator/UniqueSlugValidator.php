<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Symfony\Validator;

use AlexandreBulete\DddBlogBundle\Domain\Repository\CategoryRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Repository\PostRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\SlugGeneratorInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\Slug;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\CategoryResource;
use AlexandreBulete\DddBlogBundle\Infrastructure\Sylius\Resource\PostResource;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class UniqueSlugValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly CategoryRepositoryInterface $categories,
        private readonly SlugGeneratorInterface $slugger,
    ) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueSlug) {
            throw new UnexpectedTypeException($constraint, UniqueSlug::class);
        }
        if (!$value instanceof PostResource && !$value instanceof CategoryResource) {
            return;
        }

        $typed = $value->slug !== null && trim($value->slug) !== '';
        $source = $typed ? $value->slug : ($value instanceof PostResource ? $value->title : $value->name);
        if ($source === null || trim($source) === '') {
            return;
        }

        try {
            $slug = $typed ? Slug::fromString($source) : $this->slugger->slugify($source);
        } catch (\InvalidArgumentException) {
            // A malformed slug is the Regex constraint's to report.
            return;
        }

        $holder = $value instanceof PostResource ? $this->posts->findBySlug($slug) : $this->categories->findBySlug($slug);
        if ($holder === null || ($value->id !== null && $holder->id->value()->equals($value->id))) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ slug }}', $slug->value())
            ->atPath($typed ? 'slug' : ($value instanceof PostResource ? 'title' : 'name'))
            ->addViolation();
    }
}
