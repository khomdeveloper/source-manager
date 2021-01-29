<?php

namespace SourceManager;

use SourceManager\Interfaces\MinificatorInterface;


class CssManager extends AbstractPageSource
{
    const BASE_SOURCE_FILE_EXTENSION = 'css';

    protected array $compilers = [];

    /**
     * @return string
     */
    public function getSourceDescriptionFileName(): string
    {
        return 'styles.json';
    }

    /**
     * @return array
     */
    protected function availableSourceExtensions(): array
    {
        return array_merge([self::BASE_SOURCE_FILE_EXTENSION], array_keys($this->compilers));
    }

    public function __construct(?Settings $settings = null, ?MinificatorInterface $minificator = null, array $compilers = [])
    {

        parent::__construct($settings, $minificator);

        if (!empty($compilers) && is_array($compilers)) {
            $this->compilers = $compilers;
        } elseif (!empty($this->settings)) {

            $defaultCompilers = $this->settings->get(self::class, 'compilers');

            if (!empty($defaultCompilers) && is_array($defaultCompilers)) {
                foreach ($defaultCompilers as $ext => $class) {
                    $this->compilers[$ext] = new $class(); //@todo: import source dir
                }
            } else {
                $this->compilers = [];
            }

        } else {
            $this->compilers = [];
        }
    }

    protected function preProcess(string $source): string
    {
        $css = '';

        if (isset($this->compilers[pathinfo($source, PATHINFO_EXTENSION)])) {
            $compiler = $this->compilers[pathinfo($source, PATHINFO_EXTENSION)];
            $css = $compiler->refresh()->compile($source);
        }

        return $css;
    }

    public function getLoadMode($source): array
    {
        if (is_string($source) && strpos($source, 'async') !== false) {
            return [
                'source' => $source,
                'attributes' => 'media="print" onload="this.media=\'all\';"'
            ];
        } else {
            return parent::getLoadMode($source);
        }
    }


}