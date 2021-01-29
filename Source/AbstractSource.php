<?php

namespace SourceManager;

use BaseClass\BaseClass;
use BaseClass\FileService;
use BaseClass\PathService;
use BaseClass\StaticStringService;
use phpDocumentor\Reflection\DocBlock\Tags\Source;

abstract class AbstractSource
{

    //@TODO: user redis as cache

    const AUTOTEST_PATH = '/Tests/';

    const AUTOTEST_SELF = '/phpunit';

    const PATH_TO_CACHE = '/../cache';

    const FILES_CASH_SUFFIX = '_files.json';

    const ASSEMBLED_CASH_SUFFIX = '_assembled.json';

    protected bool $sourceChanged = false;

    protected ?Settings $settings = null;

    protected $loadedSourceFiles = [];

    public function __construct(?Settings $settings = null)
    {
        $this->settings = $settings;
    }

    /**
     * @return Settings|null
     */
    public function getSettings(): ?Settings
    {
        return $this->settings;
    }


    /**
     * @param $fileName
     * @return bool
     */
    protected function ignoreFileIfNotAutotest($fileName): bool
    {
        if (strpos($_SERVER['PHP_SELF'], static::AUTOTEST_SELF) !== false) {
            return false;
        }

        if (strpos($fileName, static::AUTOTEST_PATH) !== false) {
            return true;
        } else {
            return false;
        }

    }

    public function getPathToData()
    {
        if (!empty($this->settings)) {
            $cachePath = PathService::getRootPath() . $this->settings->get(self::class, 'cache');
        } else {
            $cachePath = __DIR__ . static::PATH_TO_CACHE;
        }

        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        return $cachePath;

    }

    public function getFilesCashName()
    {
        return $this->getPathToData() . '/' . strtolower(StaticStringService::shortClassName(get_called_class())) . static::FILES_CASH_SUFFIX;
    }

    public function getAssembledSourceCacheName()
    {
        return $this->getPathToData() . '/' . strtolower(StaticStringService::shortClassName(get_called_class())) . static::ASSEMBLED_CASH_SUFFIX;
    }


    /**
     * if some of files listed in _files.json younger than _files.json itself - regenerate sources
     *
     * @return mixed|null
     */
    protected function getCachedSourceFiles()
    {

        $filesCacheName = $this->getFilesCashName();

        if (!file_exists($filesCacheName)) {
            return null;
        } else {

            $files = json_decode(file_get_contents($filesCacheName), true);

            if (empty($files)) {
                return null;
            }

            $cacheCreatedAt = filemtime($filesCacheName);

            $this->loadedSourceFiles = [];

            foreach ($files as $record) {

                //if we remove some source files
                if (!file_exists(key($record))) {
                    return null;
                }

                //check that files described in cache older that cache
                if ((int)$cacheCreatedAt < filemtime(key($record))) {
                    return null;
                }

                $this->loadedSourceFiles[key($record)] = true;
            }

            //check new source appear
            if ($this->settings && $this->detectNewSource($this->settings->get(self::class, 'observe'))) {
                return null;
            }

            return $files;
        }

    }


    protected function detectNewSource($observedDirs)
    {
        if (!empty($observedDirs)) {
            if (is_string($observedDirs)) {
                $observedDirs = [$observedDirs];
            }

            foreach ($observedDirs as $folder) {
                $path = PathService::getRootPath() . $folder;
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $file = $path . '/' . $this->getSourceDescriptionFileName();
                if (!isset($this->loadedSourceFiles[$file]) && file_exists($file)) {
                    return true;
                }
            }
        }

        return false;
    }


    abstract protected function getSourceDescriptionFileName(): string;


    protected function findSourceFiles(?string $path = null, string $fileName = '')
    {

        if (empty($fileName)) {
            $fileName = $this->getSourceDescriptionFileName();
        }

        $cachedSourceFiles = $this->getCachedSourceFiles();

        if (empty($cachedSourceFiles)) {

            $this->sourceChanged = true;

            if (empty($path)) {
                $path = PathService::getRootPath();
            }

            $list = FileService::getFileList($path, $fileName);

            $result = [];
            foreach ($list ?? [] as $fileName => $record) {
                if ($this->ignoreFileIfNotAutotest($fileName)) {
                    continue;
                }
                $result[] = [$fileName => $record['changed']];
            }

            file_put_contents($this->getFilesCashName(), json_encode($result));

            return $result;

        } else {

            $this->sourceChanged = false;

            return $cachedSourceFiles;
        }

    }


    protected function assembleSources(array $list): array
    {

        usort($list, function ($a, $b) {
            return current($a) <=> current($b);
        });

        $sources = [];
        foreach ($list as $record) {
            $data = json_decode(file_get_contents(key($record)), true);
            if (!empty($data)) {
                $sources = array_merge($sources, $data);
            }
        }

        file_put_contents($this->getAssembledSourceCacheName(), json_encode($sources));

        return $sources;

    }


    public function getSources(): array
    {

        $list = $this->findSourceFiles();

        if (empty($list)) {
            throw new FileNotFound([
                'filename' => $this->getSourceDescriptionFileName()
            ]);
        }

        $routesCache = $this->getAssembledSourceCacheName();

        if (!$this->sourceChanged && file_exists($routesCache)) {
            return json_decode(file_get_contents($routesCache), true);
        } else {
            return $this->assembleSources($list);
        }

    }


}