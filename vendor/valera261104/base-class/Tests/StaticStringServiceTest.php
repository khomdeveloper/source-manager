<?php

namespace BaseClass\Tests;


use BaseClass\StaticStringService;

class StaticStringServiceTest extends \PHPUnit\Framework\TestCase
{

    public function testCamelCaseToUnderscore()
    {
            $string = 'SomeCamelCaseString';

            $result = StaticStringService::camelCaseToUnderScore($string, ' ');

            $this->assertEquals('some camel case string', $result);

    }

    public function testAddFirstSlash()
    {

        $this->assertEquals('/', StaticStringService::addFirstSlash(''));

        $this->assertEquals('/', StaticStringService::addFirstSlash('/'));

        $this->assertEquals('/some/path', StaticStringService::addFirstSlash('some/path'));

        $this->assertEquals('/some/path', StaticStringService::addFirstSlash('/some/path'));

    }

    public function testRemoveFinalSlash()
    {
        $this->assertEquals('/', StaticStringService::removeFinalSlash('/'));

        $this->assertEquals('some', StaticStringService::removeFinalSlash('some/'));

        $this->assertEquals('some', StaticStringService::removeFinalSlash('some//'));

        $this->assertEquals('/', StaticStringService::removeFinalSlash(''));
    }

}