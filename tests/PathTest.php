<?php

namespace Tests\CoRex\Filesystem;

use CoRex\Filesystem\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    private $rootDirectory;
    private $currentVendor;
    private $currentPackage;
    private $vendorBaseDirectory;

    /**
     * Setup.
     */
    protected function setUp()
    {
        parent::setUp();

        // Prepare root directory.
        $this->rootDirectory = __DIR__;
        for ($c1 = 0; $c1 < 3; $c1++) {
            $this->rootDirectory = dirname($this->rootDirectory);
        }
        $this->vendorBaseDirectory = basename($this->rootDirectory);
        $this->rootDirectory = dirname($this->rootDirectory);

        // Get package details.
        $packagePath = dirname(__DIR__);
        $this->currentPackage = basename($packagePath);
        $this->currentVendor = basename(dirname($packagePath));
    }

    /**
     * Test get root.
     */
    public function testRoot()
    {
        $this->assertEquals($this->rootDirectory, Path::root());
        $this->assertEquals($this->rootDirectory . '/test1/test2', Path::root(['test1', 'test2']));
        $this->assertEquals($this->rootDirectory . '/test1/test2', Path::root('test1.test2'));
    }

    /**
     * Test package current.
     */
    public function testPackageCurrent()
    {
        $expected = $this->rootDirectory . '/' . $this->vendorBaseDirectory;
        $expected .= '/' . $this->currentVendor . '/' . $this->currentPackage;
        $this->assertEquals(
            $expected,
            Path::packageCurrent()
        );
    }

    /**
     * Test package.
     */
    public function testPackage()
    {
        $this->assertEquals(
            $this->rootDirectory . '/' . $this->vendorBaseDirectory . '/test1/test2',
            Path::package('test1', 'test2')
        );
    }

    /**
     * Test package segments as array.
     */
    public function testPackageSegmentsAsArray()
    {
        $this->assertEquals(
            $this->rootDirectory . '/' . $this->vendorBaseDirectory . '/test1/test2/a/b/c/d',
            Path::package('test1', 'test2', ['a', 'b', 'c', 'd'])
        );
    }

    /**
     * Test package segments as dot notation.
     */
    public function testPackageSegmentsAsDotNotation()
    {
        $this->assertEquals(
            $this->rootDirectory . '/' . $this->vendorBaseDirectory . '/test1/test2/a/b/c/d',
            Path::package('test1', 'test2', 'a.b.c.d')
        );
    }

    /**
     * Test package segments as dot notation.
     */
    public function testPackageSegmentsAsString()
    {
        $this->assertEquals(
            $this->rootDirectory . '/' . $this->vendorBaseDirectory . '/test1/test2/a/b/c/d',
            Path::package('test1', 'test2', 'a/b/c/d')
        );
    }

    /**
     * Test package segments as single.
     */
    public function testPackageSegmentsAsSingle()
    {
        $this->assertEquals(
            $this->rootDirectory . '/' . $this->vendorBaseDirectory . '/test1/test2/a',
            Path::package('test1', 'test2', 'a')
        );
    }

    /**
     * Test vendor name.
     */
    public function testVendorName()
    {
        $this->assertEquals($this->currentVendor, Path::vendorName());
    }

    /**
     * Test package name.
     */
    public function testPackageName()
    {
        $this->assertEquals($this->currentPackage, Path::packageName());
    }
}
