<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Application\Service;

use AlexandreBulete\DddBlogBundle\Domain\Model\Tag;
use AlexandreBulete\DddBlogBundle\Domain\Repository\TagRepositoryInterface;
use AlexandreBulete\DddBlogBundle\Domain\Service\SlugGeneratorInterface;
use AlexandreBulete\DddBlogBundle\Domain\ValueObject\TagId;

/**
 * Turns the tag names typed on a post into tag identifiers, creating the tags
 * nobody used before. A name is matched by its slug: "Polar", "polar " and
 * "POLAR" are one tag, named the first way it was typed.
 */
final readonly class TagResolver
{
    public function __construct(
        private TagRepositoryInterface $tags,
        private SlugGeneratorInterface $slugger,
    ) {}

    /**
     * @param list<string> $names
     *
     * @return list<TagId> in the order typed, without duplicates
     */
    public function resolve(array $names, \DateTimeImmutable $at): array
    {
        $ids = [];
        foreach ($names as $name) {
            if (trim($name) === '') {
                continue;
            }

            $slug = $this->slugger->slugify($name);
            if (isset($ids[$slug->value()])) {
                continue;
            }

            $tag = $this->tags->findBySlug($slug);
            if ($tag === null) {
                $tag = Tag::create(TagId::generate(), $name, $slug, $at);
                $this->tags->save($tag);
            }
            $ids[$slug->value()] = $tag->id;
        }

        return array_values($ids);
    }
}
