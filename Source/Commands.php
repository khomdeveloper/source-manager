<?php


namespace SourceManager;


use BaseClass\PathService;

class Commands extends AbstractSource
{

    protected string $path = '/';

    public function __construct(?Settings $settings = null)
    {

        parent::__construct($settings ?? new Settings());

        $path = $this->settings->get(self::class, 'sourcePath');

        if (!empty($path) && is_string($path)) {
            $this->path = $path;
        }
    }

    protected function getSourceDescriptionFileName(): string
    {
        return 'command.json';
    }

    protected function getCommandFileName()
    {
        return PathService::getRootPath() . $this->path . '/' . $this->getSourceDescriptionFileName();
    }

    public function getSources(): array
    {
        $path = PathService::getRootPath() . $this->path;

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $commandFile = $path . '/' . $this->getSourceDescriptionFileName();

        return file_exists($commandFile)
            ? json_decode(file_get_contents($commandFile), true)
            : [];
    }

    public function process()
    {
        $commands = $this->getSources();

        if (empty($commands)) {
            return false;
        }

        $executed = false;

        foreach ($commands as $class => $executed)
        {
            if (class_exists($class)) {
                if (!empty($executed)) {
                    continue;
                } else {
                    (new $class($this->settings))->execute();
                    $executed = true;
                    $commands[$class] = time();
                }
            }
        }

        if ($executed) {
            file_put_contents($this->getCommandFileName(), json_encode($commands));
            return true;
        }

        return false;
    }

}