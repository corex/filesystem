<?php

namespace Tests\CoRex\Filesystem;

use CoRex\Filesystem\Directory;
use CoRex\Filesystem\File;
use CoRex\Filesystem\Json;
use CoRex\Helpers\Obj;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    private $tempDirectory;
    private $tempFilename;

    /**
     * Test constructor.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testConstructor()
    {
        $json = new Json($this->tempFilename);
        $this->assertEquals($this->tempFilename, Obj::getProperty('filename', $json));
        $this->assertEquals([], Obj::getProperty('keyOrder', $json));
        $this->assertEquals([], Obj::getProperty('data', $json));
    }

    /**
     * Test keyOrder value 1-2.
     *
     * @throws \Exception
     */
    public function testKeyOrderValueOneToTwo()
    {
        $value1 = md5(mt_rand(1, 100000)) . '1';
        $value2 = md5(mt_rand(1, 100000)) . '2';
        $json = new Json($this->tempFilename, ['value1', 'value2']);
        $json->set('value1', $value1);
        $json->set('value2', $value2);
        $json->save();
        $data = File::getJson($this->tempFilename);
        $this->assertEquals([
            'value1' => $value1,
            'value2' => $value2
        ], $data);
    }

    /**
     * Test keyOrder value 2-1.
     *
     * @throws \Exception
     */
    public function testKeyOrderValueTwoToOne()
    {
        $value1 = md5(mt_rand(1, 100000)) . '1';
        $value2 = md5(mt_rand(1, 100000)) . '2';
        $json = new Json($this->tempFilename, ['value2', 'value1']);
        $json->set('value1', $value1);
        $json->set('value2', $value2);
        $json->save();
        $data = File::getJson($this->tempFilename);
        $this->assertEquals([
            'value2' => $value2,
            'value1' => $value1
        ], $data);
    }

    /**
     * Test save.
     *
     * @throws \Exception
     */
    public function testSave()
    {
        $value1 = md5(mt_rand(1, 100000)) . '1';
        $value2 = md5(mt_rand(1, 100000)) . '2';
        $json = new Json($this->tempFilename);
        $json->set('value1', $value1);
        $json->set('value2', $value2);
        $json->save();
        $data = File::getJson($this->tempFilename);
        $this->assertEquals([
            'value1' => $value1,
            'value2' => $value2
        ], $data);
    }

    /**
     * Setup.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->tempDirectory = sys_get_temp_dir();
        $this->tempDirectory .= '/' . str_replace('.', '', microtime(true));
        Directory::make($this->tempDirectory);
        $this->tempFilename = File::getTempFilename($this->tempDirectory, '', 'json');
    }

    /**
     * Tear down.
     */
    protected function tearDown()
    {
        parent::tearDown();
        Directory::delete($this->tempDirectory);
    }
}