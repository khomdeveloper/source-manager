<?php


namespace BaseClass\Tests;


use BaseClass\FileService;
use ExtendedException\FileNotFound;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{

    public function testGetAllFileList()
    {
        $result = FileService::getFileList(__DIR__ . '/Data');

        $this->assertIsArray($result);

        $this->assertEquals(5, count($result));
    }

    public function testGetFile()
    {
        $result = FileService::getFileList(__DIR__ . '/Data', '1.php');

        $this->assertIsArray($result);

        $this->assertEquals(2, count($result));
    }


    public function testNotFound()
    {
        $result = FileService::getFileList(__DIR__ . '/Data', '17.php');

        $this->assertIsArray($result);

        $this->assertEmpty($result);

    }

    public function testNotFoundException()
    {

        $this->expectException(\Exception::class);

        $result = FileService::getFileList(__DIR__ . '/Data', '17.php', new \Exception('File not found'));

    }


    public function testFindArrayOfFiles()
    {
        $result = FileService::getFileList(__DIR__ . '/Data', ['1.php','2.php','17.php']);

        $this->assertIsArray($result);

        $this->assertEquals(3, count($result));
    }


    public function testFindSimplePattern()
    {
        $result = FileService::getFileList(__DIR__ . '/Data', ['*.php']);

        $this->assertIsArray($result);

        $this->assertEquals(3, count($result));
    }


    public function testFindComplicatedPatternWithWildCard()
    {
        $result = FileService::getFileList(__DIR__ . '/Data', '2/*.ph*');

        $this->assertIsArray($result);

        $this->assertEquals(2, count($result));
    }

    public function testFindInDifferentPaths()
    {

        $result = FileService::getFileList([__DIR__ . '/Data',__DIR__ . '/Data/2'], ['1.php', '17.php']);

        $this->assertIsArray($result);

        $this->assertEquals(2, count($result));

    }

    public function testCallableCondition()
    {

        $result = FileService::getFileList(__DIR__ . '/Data', function($fileName){
            if (is_dir($fileName)) {
                return true;
            }
        });

        $this->assertIsArray($result);

        $this->assertEquals(1, count($result));

    }



}