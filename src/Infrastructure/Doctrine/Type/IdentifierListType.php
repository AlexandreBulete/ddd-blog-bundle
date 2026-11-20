<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * An ordered list of identifiers on a JSON column: the categories or the tags
 * of a post, in the order the author gave them.
 *
 * The post is read and written whole with its list, so it lives in the post
 * row. Filtering by category or tag goes through the index tables kept beside
 * it (blog_post_category, blog_post_tag), not through JSON operators that
 * differ on every platform. JSONB on PostgreSQL, JSON elsewhere.
 *
 * @template T of IdentifierVO
 */
abstract class IdentifierListType extends Type
{
    /**
     * @return class-string<T>
     */
    abstract protected function identifierClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonbTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Expected a list of %s, got %s.', $this->identifierClass(), get_debug_type($value)));
        }

        $ids = [];
        foreach ($value as $id) {
            if (!$id instanceof IdentifierVO) {
                throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', $this->identifierClass(), get_debug_type($id)));
            }
            $ids[] = $id->toRfc4122();
        }

        return json_encode($ids, JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<T>|null
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }
        if (!is_string($value)) {
            throw ValueNotConvertible::new($value, static::class, 'expected a JSON string');
        }

        try {
            $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValueNotConvertible::new($value, static::class, $e->getMessage(), $e);
        }
        if (!is_array($decoded) || !array_is_list($decoded)) {
            throw ValueNotConvertible::new($value, static::class, 'expected a JSON array of identifiers');
        }

        $class = $this->identifierClass();
        $ids = [];
        foreach ($decoded as $id) {
            if (!is_string($id)) {
                throw ValueNotConvertible::new($value, static::class, 'identifiers must be strings');
            }
            $ids[] = $class::fromString($id);
        }

        return $ids;
    }
}
