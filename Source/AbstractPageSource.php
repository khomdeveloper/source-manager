<?php


namespace SourceManager;


use BaseClass\PathService;
use BaseClass\StaticStringService;
use ExtendedException\FileNotFound;
use SourceManager\Exceptions\MinificatorRequired;
use SourceManager\Exceptions\WrongSourceDeclaration;
use SourceManager\Interfaces\MinificatorInterface;

abstract class AbstractPageSource extends AbstractSource
{

    protected bool $needToReassemble = false;

    protected MinificatorInterface $minificator;

    abstract protected function availableSourceExtensions(): array;

    public function __construct(?Settings $settings = null, ?MinificatorInterface $minificator = null)
    {

        parent::__construct($settings ?? new Settings());

        if (!empty($minificator)){
            $this->minificator = $minificator;
        } else {

            if (empty($this->settings)) {
                throw new MinificatorRequired();
            }

            $minificatorClass = $this->settings->get(self::class, 'minificator');

            if (empty($minificatorClass)) {
                throw new MinificatorRequired();
            }

            $this->minificator = new $minificatorClass;
        }

    }


    /**
     * @return MinificatorInterface|null
     */
    public function getMinificator()
    {
        return $this->minificator;
    }

    /**
     * @param array $array
     * @return string
     */
    protected function createAttrFromArray(array $array): string
    {
        $h = [];
        foreach ($array as $key => $val) {
            if ($key == $val) {
                $h[] = $key;
            } else {
                $h[] = $key . '="' . $val . '"';
            }
        }

        return join(' ', $h);
    }

    public function getLoadMode($source): array
    {
        if (is_string($source)) {
            return [
                'source' => $source,
                'attributes' => ''
            ];
        } elseif (is_array($source) && isset($source['source'])) {

            $src = $source['source'];

            unset($source['source']);

            return [
                'source' =>  $src,
                'attributes' => StaticStringService::createAttrFromArray($source)
            ];
        } else {
            throw new WrongSourceDeclaration();
        }
    }

    protected function preProcess(string $source): string
    {
        return '';
    }

    protected function assembleSources(array $list): array
    {

        usort($list, function ($a, $b) {
            return current($a) <=> current($b);
        });

        $sources = [];
        foreach ($list as $record) {
            $data = json_decode(file_get_contents(key($record)), true);

            $withPath = [];
            foreach ($data ?? [] as $source => $target) {

                if (strpos($source, '://') !== false) {
                    $withPath[$source] = $target;
                    continue;
                }

                if (strpos(strtolower(dirname(key($record))), '/config') !== false) {
                    //try to find up to config dir
                    $realFileName = dirname(key($record)) . '/..' . $source;
                } else {
                    //try to find in same path as source.json
                    $realFileName = dirname(key($record)) . $source;
                }

                if (!file_exists($realFileName)) {
                    //try to find under root path
                    $realFileName = PathService::getRootPath() . $source;
                    if (!file_exists($realFileName)) {
                        continue;
                    }
                }

                if (empty($target)) {
                    continue;
                }

                if (!in_array(pathinfo($realFileName, PATHINFO_EXTENSION), $this->availableSourceExtensions())) {
                    continue;
                }

                $withPath[$realFileName] = $target;
            }

            if (!empty($withPath)) {
                $sources = array_merge($sources, $withPath);
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

        if (!$this->needToReassemble && !$this->sourceChanged && file_exists($routesCache)) {
            $sources = json_decode(file_get_contents($routesCache), true);

            $routesCacheChanged = filemtime($routesCache);

            /**
             * if some sources changed
             */
            foreach (array_keys($sources) as $source) {
                if (!file_exists($source) || filemtime($source) > $routesCacheChanged) {
                    $this->needToReassemble = true;
                    return $this->getSources();
                }
            }

            return $sources;

        } else {
            $this->needToReassemble = false;
            return $this->assembleSources($list);
        }

    }


    protected function getCompileList()
    {
        $sourceDescription = $this->getSources();

        $root = $_SERVER['DOCUMENT_ROOT'];

        $sourceListFile = $this->getAssembledSourceCacheName();
        $changed = filemtime($sourceListFile);

        $compileList = [];
        foreach ($sourceDescription as $source => $target) {

            if (strpos($source,'://') !== false) {
                $compileList[$source] = $target;
                continue;
            }

            $targetFileName = $root . $target;

            if (!file_exists($targetFileName) || filemtime($targetFileName) < $changed) {

                if (!isset($compileList[$target])) {
                    $compileList[$target] = [];
                }

                if (file_exists($source)) {
                    $compileList[$target][] = $source;
                } else {
                    throw new FileNotFound([
                        'file' => $source
                    ]);
                }

            } else {
                $compileList[$target] = false;
            }
        }

        return $compileList;
    }

    public function getPageSources()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];

        $compileList = $this->getCompileList();

        $externalSources = [];

        $internalSources = [];

        foreach ($compileList as $target => $sources) {

            if (strpos($target, '://') !== false){
                if (is_array($sources)) {
                    $externalSources[] = array_merge([
                        'source' => $target
                    ], $sources);
                }
                continue; //skip external sources
            }

            if (empty($sources)) {
                continue;
            }

            $minificator = $this->getMinificator()->refresh();

            usort($sources, function ($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });


            foreach ($sources as $source) {
                $ext = pathinfo($source, PATHINFO_EXTENSION);

                if ($ext === static::BASE_SOURCE_FILE_EXTENSION) {
                    $minificator->add($source);
                } elseif (in_array($ext, $this->availableSourceExtensions())) {
                    $compiledSource = $this->preProcess($source);
                    if (!empty($compiledSource)) {
                        $minificator->add($compiledSource);
                    }
                }
            }

            $targetFileName = $root . $target;

            if (file_exists($targetFileName)) {
                unlink($targetFileName);
            } else {
                if (!file_exists(dirname($targetFileName))) {
                    mkdir(dirname($targetFileName), 0755, true);
                }
            }

            $minificator->minify($targetFileName);

            $internalSources[] = $target;

        }

        return array_merge($internalSources, $externalSources);
    }

}