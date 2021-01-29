<?php


namespace BaseClass;


use ExtendedException\FileNotFound;

class FileService
{

    public static function getFileList($where, $condition = null, ?\Exception $ifNotFound = null)
    {

        if (is_string($where)) {
            $path = $where;
        } elseif (is_array($where)) {
            $result = [];
            foreach ($where as $path) {
                $result = \array_merge($result, self::getFileList($path, $condition));
            }
            return static::throwOrReturn($result, $ifNotFound);
        }

        if (is_string($condition)) {
            $fileName = $condition;
        } elseif (is_array($condition)) {
            $result = [];
            foreach ($condition as $fileName) {
                $result = \array_merge($result, self::getFileList($path, $fileName));
            }
            return static::throwOrReturn($result, $ifNotFound);
        }

        //@todo: add isCallable

        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST);
        $files = [];
        foreach ($objects as $name => $void) {
            if (basename($name)[0] === '.') {
                continue;
            }

            if (is_callable($condition)) {

                if ($condition($name)) {
                    $files[$name] = static::getFileData($name);
                }

                continue;
            }

            if (empty($fileName)) {
                $files[$name] = static::getFileData($name);
            } else {

                if ($name == $fileName) {
                    $files[$name] = static::getFileData($name);
                    continue;
                }

                if (basename($name) == basename($fileName)) {
                    $files[$name] = static::getFileData($name);
                    continue;
                }

                if (strpos($fileName, '*') !== false) {
                    $fit = false;
                    foreach (explode('*', $fileName) as $pattern) {
                        if (!empty($pattern)) {
                            if (strpos($name, $pattern) === false) {
                                $fit = false;
                                break;
                            } else {
                                $fit = true;
                            }
                        } else {
                            $fit = true;
                        }
                    }

                    if (!empty($fit)) {
                        $files[$name] = static::getFileData($name);
                    }
                }

                if (strpos("/", $fileName) && StaticStringService::isRegex($fileName)) {
                    //@todo: check pregmatch
                }
            }
        }

        return static::throwOrReturn($files, $ifNotFound);


    }

    protected static function throwOrReturn(array $result, ?\Exception $exception = null): array
    {
        if (empty($result) && $exception instanceof \Exception) {
            throw new $exception;
        }

        return $result;
    }

    protected static function getFileData($fileName)
    {
        if (file_exists($fileName)) {
            return [
                'changed' => filemtime($fileName)
            ];
        } else {
            return [];
        }
    }

}