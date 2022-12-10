<?php

namespace App\Entities\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

use function explode;
use function implode;
use function is_resource;
use function stream_get_contents;

/**
 * Array Type which can be used for int values divided by pipe sign.
 */
class IntegerArrayType extends Type
{

    const INTEGER_ARRAY = 'integer_array';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return null;
        }

        return implode('|', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if ($value === null) {
            return [];
        }

        $value = is_resource($value) ? stream_get_contents($value) : $value;

        return array_map('intval', explode('|', $value));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::INTEGER_ARRAY;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
