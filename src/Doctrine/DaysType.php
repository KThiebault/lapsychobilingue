<?php

namespace App\Doctrine;

use App\Doctrine\Type\Day;

class DaysType extends EnumType
{
    public const NAME = 'Day';

    public static function getEnumsClass(): string
    {
        return Day::class;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}