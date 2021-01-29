<?php


namespace SourceManager;


class JavaScripts extends AbstractPageSource
{

    const BASE_SOURCE_FILE_EXTENSION = 'js';

    public function getSourceDescriptionFileName(): string
    {
        return 'scripts.json';
    }

    protected function availableSourceExtensions(): array
    {
        return [
            'js'
        ];
    }

    public function getLoadMode($source): array
    {

        if (is_string($source) && strpos($source, 'async') !== false) {
            return [
                'source' => $source,
                'attributes' => 'async'
            ];
        } elseif (is_string($source) && strpos($source, 'defer') !== false) {
            return [
                'source' => $source,
                'attributes' => 'defer'
            ];
        } else {
            return parent::getLoadMode($source);
        }
    }

}