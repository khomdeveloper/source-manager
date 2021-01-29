<?php


namespace SourceManager;


use BaseClass\PathService;
use SourceManager\Commands\RemoveCacheCommand;

class Settings extends AbstractSource
{

    protected array $loadedSettingsFiles = [];

    protected array $allSettings=[];

    public function __construct()
    {
        $this->settings = null;
        $this->allSettings = $this->getSources();
    }

    public function getSources(): array
    {
        $sources = parent::getSources();

        $observedDirs = $sources[parent::class]['observed'] ?? [];

        if ($this->detectNewSource($observedDirs)) {
            return $this->removeCache()->getSources();
        }

        return $sources;
    }

    /**
     * @return $this
     */
    public function removeCache()
    {
        (new RemoveCacheCommand())->execute($this->getPathToData());
        return $this;
    }


    /**
     * @param string|null $class
     * @return array
     */
    public function get(?string $class = null, ?string $parameter = null)
    {
        if (empty($class)) {
            return $this->allSettings;
        }

        if (!isset($this->allSettings[$class])) {
            return empty($parameter)
                ? null
                : [];
        }

        if (empty($parameter)) {
            return $this->allSettings[$class];
        } else {
            return $this->allSettings[$class][$parameter] ?? null;
        }

    }

    /**
     * @return string
     */
    public function getSourceDescriptionFileName(): string
    {
       return "settings.json";
    }

}