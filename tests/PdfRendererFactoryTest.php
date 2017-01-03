<?php

class PdfRendererFactoryTest extends \PHPUnit_Framework_TestCase {
    private $root;
    private $factory;

    protected function setUp() {
        $this->pdfRendererBinaryMock = $this->getMockBuilder(Services\PdfRendererBinaryInterface::class)
            ->setMethods(['getPath'])
            ->getMock();

        $this->factory = new Services\PdfRendererFactory($this->pdfRendererBinaryMock);
    }

    public function testCreate() {
        $this->pdfRendererBinaryMock
            ->method('getPath')
            ->willReturn('foo.bin');
        $pdfRenderer = $this->factory->create();
        $this->assertSame('foo.bin', $pdfRenderer->binary);
    }
}