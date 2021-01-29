<?php


namespace BaseClass;


class StringService
{

    public function camelCaseToUnderScore(string $string, string $delimeter = '_')
    {
        return StaticStringService::camelCaseToUnderScore($string, $delimeter);
    }

    public function isCapital($letter)
    {
        return StaticStringService::isCapital($letter);
    }

    public function shortClassName($className)
    {
        return StaticStringService::shortClassName($className);
    }

    public function underScoreToCamelCase($string)
    {
        return StaticStringService::underScoreToCamelCase($string);
    }

}