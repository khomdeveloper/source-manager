<?php


namespace BaseClass;


class DataTypeService
{

    public static function isAssociativeArray($a): bool
    {
        if (!is_array($a)) {
            return false;
        }

        if (\array_values($a) == $a) {
            return false;
        } else {
            return true;
        }

    }

    public static function isNumericArray($a): bool
    {
        if (!is_array($a)) {
            return false;
        }

        return !self::isAssociativeArray($a);

    }

}