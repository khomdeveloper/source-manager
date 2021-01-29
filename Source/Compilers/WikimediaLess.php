<?php

namespace SourceManager\Compilers;

use SourceManager\Interfaces\CssCompilerInterface;

class WikimediaLess implements CssCompilerInterface
{

    private $compiler;

    protected string $cssPath = '/';

    public function __construct(string $cssPath = '/')
    {
        $this->cssPath = $cssPath;
        $this->compiler = new \Less_Parser();
    }

    public function refresh(): self
    {
        $this->compiler = new \Less_Parser();

        return $this;
    }


    public function compile(string $source, ?string $target = null):string
    {
        $this->compiler->parseFile($source, $this->cssPath);
        $css = $this->compiler->getCss();

        if (!empty($target)){
            if (!file_exists(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }
            file_put_contents($target, $css);
        }

        return $css;

    }

}