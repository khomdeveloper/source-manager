<?php

namespace SourceManager\Minificators;

use MatthiasMullie\Minify;
use SourceManager\Interfaces\MinificatorInterface;

class MullieMinificator implements MinificatorInterface
{

    private $minificator;

    /**
     * MullieMinificator constructor.
     */
    public function __construct()
    {
        $this->minificator = new Minify\CSS();
    }

    /**
     * @return Minify\CSS
     */
    public function getMinificator(): Minify\CSS
    {
        return $this->minificator;
    }

    /**
     * @param $source
     * @return $this
     */
    public function add($source):self
    {
        $this->minificator->add($source);

        return $this;
    }

    /**
     * @param null $path
     * @return string
     */
    public function minify($path = null):string
    {
        return $this->minificator->minify($path);
    }

    /**
     * @return $this
     */
    public function refresh():self
    {
        $this->minificator = new Minify\CSS();

        return $this;
    }

}