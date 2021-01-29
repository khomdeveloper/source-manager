<?php


namespace BaseClass;


abstract class BaseClass
{


    /**
     * @param array $input
     * @return $this
     */
    public function set(array $input = []): self
    {
        if (method_exists($this, 'validate')) {
            $this->validate($input);
        }

        foreach ($input as $key => $val) {
            $this->$key = $val;
        }

        return $this;
    }


    /**
     * @param string $property
     * @return |null
     */
    public function get(string $property)
    {
        try {
            return $this->$property;
        } catch (\Throwable $e) {
            return null;
        }
    }


    public function getShortClassName(string $className = '')
    {
        if (empty($className)) {
            $className = get_called_class();
        }

        return StaticStringService::shortClassName($className);
    }

    public function getRootPath()
    {
        return PathService::getRootPath();
    }


}