<?php

declare(strict_types=1);

namespace Tests\CoRex\Filesystem;

use CoRex\Filesystem\Directory;
use CoRex\Filesystem\File;
use PHPUnit\Framework\TestCase;

class DirectoryTest extends TestCase
{
    /** @var string */
    private $tempDirectory;

    /** @var string */
    private $filename1;

    /** @var string */
    private $filename2;

    /**
     * Setup.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDirectory = sys_get_temp_dir();
        $this->tempDirectory .= '/' . str_replace('.', '', microtime(true));

        // Create unique filenames.
        $uniqueCode = md5(str_replace('.', '', microtime(true)));
        $this->filename1 = $uniqueCode . '1';
        $this->filename2 = $uniqueCode . '2';
    }

    /**
     * Tear down.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Directory::delete($this->tempDirectory);
    }

    /**
     * Test exist.
     */
    public function testExist(): void
    {
        $this->assertFalse(Directory::exist($this->tempDirectory));
        mkdir($this->tempDirectory);
        $this->assertTrue(Directory::exist($this->tempDirectory));
    }

    /**
     * Test exist.
     */
    public function testIsDirectory(): void
    {
        $this->assertFalse(Directory::isDirectory($this->tempDirectory));
        mkdir($this->tempDirectory);
        $this->assertTrue(Directory::isDirectory($this->tempDirectory));
    }

    /**
     * Test is writeable.
     */
    public function testIsWritable(): void
    {
        $this->assertFalse(Directory::isWritable($this->tempDirectory));
        mkdir($this->tempDirectory);
        $this->assertTrue(Directory::isWritable($this->tempDirectory));
    }

    /**
     * Test make.
     */
    public function testMake(): void
    {
        $this->assertFalse(Directory::exist($this->tempDirectory));
        Directory::make($this->tempDirectory);
        $this->assertTrue(Directory::exist($this->tempDirectory));
    }

    /**
     * Test entries.
     */
    public function testEntries(): void
    {
        $this->createTestData('test');

        // Check entries.
        $entries = Directory::entries($this->tempDirectory, '*', Directory::TYPE_FILE, true);
        $checkEntries = [
            $this->filename1,
            $this->filename2,
        ];
        $this->assertCount(2, $entries);
        foreach ($entries as $entry) {
            $this->assertTrue(in_array($entry['name'], $checkEntries));
        }
    }

    /**
     * Test entries not path.
     */
    public function testEntriesNotPath(): void
    {
        $entries = Directory::entries(null, '*');
        $this->assertEquals([], $entries);
    }


    /**
     * Test entries no criteria and type string.
     */
    public function testEntriesNoCriteriaMatchAndTypeString(): void
    {
        $this->createTestData('test');

        // Check entries.
        $entries = Directory::entries($this->tempDirectory, 'unknown*', [], true);
        $this->assertEquals([], $entries);
    }

    /**
     * Test delete.
     */
    public function testDeleteClean(): void
    {
        $this->createTestData('test');
        $this->createTestData('test2');

        // Crate symbolic link in '/test2'.
        $filename = $this->tempDirectory . '/test2/' . $this->filename1;
        symlink($filename, $filename . '.lnk');

        Directory::clean($this->tempDirectory . '/test');
        Directory::delete($this->tempDirectory . '/test2');

        $this->assertFalse(File::exist($filename . '.lnk'));

        $this->assertTrue(Directory::exist($this->tempDirectory . '/test'));
        $this->assertFalse(Directory::exist($this->tempDirectory . '/test2'));

        $this->assertFalse(Directory::delete(null));
    }

    /**
     * Test temp().
     */
    public function testTemp(): void
    {
        $this->assertEquals(sys_get_temp_dir(), Directory::temp());
    }

    /**
     * Create data.
     *
     * @param string $subDirectory
     */
    private function createTestData(string $subDirectory): void
    {
        Directory::make($this->tempDirectory . '/' . $subDirectory);
        file_put_contents($this->tempDirectory . '/' . $this->filename1, 'test');
        file_put_contents($this->tempDirectory . '/' . $subDirectory . '/' . $this->filename2, 'test');
    }
}
