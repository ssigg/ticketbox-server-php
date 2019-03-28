<?php

class FilePersisterTest extends \PHPUnit_Framework_TestCase {
    private $existingFilePath;
    private $existingFileContent;
    private $persister;

    protected function setUp() {
        $this->existingFilePath = 'foo.txt';
        $this->existingFileContent = 'foo';
        file_put_contents($this->existingFilePath, $this->existingFileContent);

        $this->persister = new Services\FilePersister();
    }

    protected function tearDown() {
        unlink($this->existingFilePath);
    }

    public function testRead() {
        $content = $this->persister->read($this->existingFilePath);
        $this->assertSame($this->existingFileContent, $content);
    }

    public function testWrite() {
        $content = 'bar';
        $filePath = 'bar.txt';
        $this->persister->write($filePath, $content);
        $this->assertSame(true, file_exists($filePath));
        unlink($filePath);
    }

    public function testWritePng() {
        $filePath = 'bar.png';
        $image = imagecreatetruecolor(10, 10);
        $this->persister->writePng($filePath, $image);
        $this->assertSame(true, file_exists($filePath));
        unlink($filePath);
    }

    public function testDelete() {
        $filePathToBeDeleted = 'fileToBeDeleted.txt';
        file_put_contents($filePathToBeDeleted, 'bar');
        $this->persister->delete($filePathToBeDeleted);
        $this->assertSame(false, file_exists($filePathToBeDeleted));
    }

    public function testExists() {
        $exists = $this->persister->exists($this->existingFilePath);
        $this->assertSame(true, $exists);
    }

    public function testExistsNot() {
        $exists = $this->persister->exists('not/existing.txt');
        $this->assertSame(false, $exists);
    }
}