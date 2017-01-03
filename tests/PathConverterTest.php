<?php

class PathConverterTest extends \PHPUnit_Framework_TestCase {
    private $root;
    private $persister;

    protected function setUp() {
        $this->root = 'root';
        $this->converter = new Services\PathConverter($this->root);
    }

    public function testConvert() {
        $relativePath = 'foo.txt';
        $absolutePath = $this->converter->convert($relativePath);
        $this->assertSame($this->root . '/' . $relativePath, $absolutePath);
    }
}