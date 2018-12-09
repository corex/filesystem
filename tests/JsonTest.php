<?php

declare(strict_types=1);

namespace Tests\CoRex\Filesystem;

use CoRex\Filesystem\Directory;
use CoRex\Filesystem\File;
use CoRex\Filesystem\Json;
use CoRex\Helpers\Obj;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    /** @var string */
    private $tempDirectory;

    /** @var string */
    private $tempFilename;

    /**
     * Test.
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testConstructor(): void
    {
        $json = new Json($this->tempFilename);
        $this->assertEquals($this->tempFilename, Obj::getProperty('filename', $json));
        $this->assertEquals([], Obj::getProperty('keyOrder', $json));
        $this->assertEquals([], Obj::getProperty('data', $json));
    }

    /**
     * Test getFilename.
     *
     * @throws \Exception
     */
    public function testGetFilename(): void
    {
        $json = new Json($this->tempFilename);
        $this->assertEquals($this->tempFilename, $json->getFilename());
    }

    /**
     * Test exist.
     *
     * @throws \Exception
     */
    public function testExist(): void
    {
        // Make sure it is removed.
        File::delete($this->tempFilename);

        $json = new Json($this->tempFilename);
        $this->assertFalse($json->exist());
        $json->save();
        $this->assertTrue($json->exist());
    }

    /**
     * Test keyOrder value 1-2.
     *
     * @throws \Exception
     */
    public function testKeyOrderValueOneToTwo(): void
    {
        $value1 = md5((string)mt_rand(1, 100000)) . '1';
        $value2 = md5((string)mt_rand(1, 100000)) . '2';
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
    public function testKeyOrderValueTwoToOne(): void
    {
        $value1 = md5((string)mt_rand(1, 100000)) . '1';
        $value2 = md5((string)mt_rand(1, 100000)) . '2';
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
    public function testSave(): void
    {
        $value1 = md5((string)mt_rand(1, 100000)) . '1';
        $value2 = md5((string)mt_rand(1, 100000)) . '2';
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
    protected function setUp(): void
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
    protected function tearDown(): void
    {
        parent::tearDown();
        Directory::delete($this->tempDirectory);
    }
}