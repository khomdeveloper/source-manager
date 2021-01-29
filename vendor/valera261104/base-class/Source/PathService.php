<?php


namespace BaseClass;



class PathService
{

    public static function getRootPath(): string
    {

        return explode('/vendor',__DIR__)[0];

    }

    public function getPathParents($path, $parents = [])
    {

        $closestParent = dirname($path);

        $parents[] = $closestParent;

        if (!strpos($closestParent, "/")) {
            return $parents;
        } else {
            return $this->getPathParents($closestParent, $parents);
        }

    }

    public function getPathLevel($path)
    {
        $a = explode("/", $path);
        return count($a) - 1;
    }

}