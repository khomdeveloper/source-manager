<?php

namespace ExtendedException\Tests;

use ExtendedException\FileNotFound;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{

    //./vendor/bin/phpunit --testdox tests

    public function testCallException()
    {

        $ex = new FileNotFound();

        $message = \json_decode($ex->getMessage(), true);

        $this->assertEquals('File not found', current($message)['en']);

    }

    public function testExceptionWithData()
    {

        $ex = new FileNotFound(['file' => 'somefile']);

        $message = \json_decode($ex->getMessage(), true);

        $this->assertEquals(\json_encode(['file' => 'somefile']),  current($message)['data']['json']);

    }

}