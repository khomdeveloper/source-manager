<?php


namespace SourceManager\Commands;

use BaseClass\FileService;
use BaseClass\PathService;
use SourceManager\AbstractSource;
use SourceManager\Interfaces\CommandInterface;
use SourceManager\Settings;

class RemoveCacheCommand implements CommandInterface
{

    protected  ?Settings $settings = null;

    public function __construct(?Settings $settings = null)
    {
        $this->settings = $settings ?? new Settings();
    }

    public function execute($path = null)
    {

        if (empty($path)) { //remove all
            $this->settings->removeCache(); //remove settings cache
            $path = PathService::getRootPath() . $this->settings->get(AbstractSource::class,'cache');
        }

        $list = FileService::getFileList($path);

        foreach($list as $file => $void) {
            unlink($file);
        }

        return true;

    }

}