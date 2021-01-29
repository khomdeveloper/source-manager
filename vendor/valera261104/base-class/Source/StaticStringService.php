<?php


namespace BaseClass;


class StaticStringService
{

    /**
     * @param string|null $string
     * @return string
     */
    public static function addFirstSlash(?string $string):string
    {
        if (empty($string)) {
            return '/';
        } elseif (str_split($string)[0] != '/'){
            return '/' . $string;
        } else {
            return $string;
        }
    }

    /**
     * @param string|null $string
     * @return string
     */
    public static function removeFinalSlash(?string $string): string
    {
        if (empty($string)) {
            return '/';
        } elseif ($string === '/') {
            return '/';
        } else {
            return rtrim($string,'/');
        }
    }

    /**
     * @param string $string
     * @param string $delimeter
     * @return string
     */
    public static function camelCaseToUnderScore(string $string, string $delimeter = '_')
    {
        $array = str_split(lcfirst($string),1);

        $result = [];
        for ($i = 0; $i < count($array); $i++){
            $letter = $array[$i];
            if (self::isCapital($letter)){
                $result[] = $delimeter . strtolower($letter);
            } else {
                $result[] = $letter;
            }
        }

        return join('', $result);
    }

    /**
     * @param $letter
     * @return bool
     */
    public static function isCapital($letter)
    {
        return strtoupper($letter) == $letter;
    }

    /**
     * @param $className
     * @return mixed
     */
    public static function shortClassName($className)
    {
        if (strpos($className,"\\") == false){
            return $className;
        }

        $a = explode("\\", $className);

        return $a[count($a)-1];
    }

    /**
     * @param $string
     * @return string
     */
    public static function underScoreToCamelCase($string)
    {
        $a = explode('_', $string);

        $b = [];

        foreach ($a as $val) {
            $b[] = ucfirst($val);
        }

        return lcfirst(join('', $b));

    }

    /**
     * @param $pattern
     * @return bool
     */
    public static function isRegex($pattern)
    {
        if (@preg_match($pattern, null) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $array
     * @return string
     */
    public static function createAttrFromArray(array $array): string
    {
        $h = [];
        foreach ($array as $key => $val) {
            if ($key == $val) {
                $h[] = $key;
            } else {
                $h[] = $key . '="' . $val . '"';
            }
        }

        return join(' ', $h);
    }

}