<?php

namespace App\Doctrine;

use App\Doctrine\Type\Day;
use BackedEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LogicException;

abstract class EnumType extends Type
{
    abstract public static function getEnumsClass(): string;

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TEXT';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (false === enum_exists($this->getEnumsClass())) {
            throw new LogicException('This ' . $this->getEnumsClass() . ' should be an enum.');
        }

        return $this::getEnumsClass()::tryFrom($value);
    }
}