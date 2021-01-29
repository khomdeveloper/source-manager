<?php

namespace SourceManager\Interfaces;

interface MinificatorInterface
{

    /**
     * @return $this
     */
    public function refresh():self;

    /**
     * @param $source
     * @return $this
     */
    public function add($source):self;

    /**
     * @param null $path
     * @return string
     */
    public function minify($path = null):string;

}