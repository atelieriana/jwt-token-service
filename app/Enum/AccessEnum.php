<?php

namespace App\Enum;
use ReflectionClass;

enum AccessEnum
{
    const ALL = 1;
    const CREATE = 2;
    const UPDATE = 3;
    const DELETE = 4;
    const READ = 5;

    public static function getDescription($value)
    {
        $reflection = new ReflectionClass(__CLASS__);
        $constants = $reflection->getConstants();
        
        foreach ($constants as $constantName => $constantValue) {
            if ($constantValue === $value) {
                return $constantName;
            }
        }
    }
}
