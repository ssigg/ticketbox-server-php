<?php

class PdfRendererBinaryTest extends \PHPUnit_Framework_TestCase {
    private $pdfPath;
    private $unique_id;
    private $reservation;

    protected function setUp() {
        $this->operatingSystemMock = $this->getMockBuilder(\Tivie\OS\DetectorInterface::class)
            ->setMethods(['getType', 'getFamily'])
            ->getMockForAbstractClass();
        
        $this->binary = new Services\PdfRendererBinary($this->operatingSystemMock);
    }

    public function testOSX() {
        $this->operatingSystemMock
            ->method('getType')
            ->willReturn(\Tivie\OS\MACOSX);
        $path = $this->binary->getPath();
        $this->assertContains('vendor/message/bin/wkhtmltopdf-osx', $path);
    }

    public function testLinux() {
        $this->operatingSystemMock
            ->method('getType')
            ->willReturn(42);
        $this->operatingSystemMock
            ->method('getFamily')
            ->willReturn(\Tivie\OS\UNIX_FAMILY);
        $path = $this->binary->getPath();
        $this->assertContains('vendor/message/bin/wkhtmltopdf-i386', $path);
    }

    public function testThrowsExceptionIfNotOSXOrLinux() {
        $this->operatingSystemMock
            ->method('getType')
            ->willReturn(42);
        $this->operatingSystemMock
            ->method('getFamily')
            ->willReturn(42);
        $this->setExpectedException(\Exception::class);
        $path = $this->binary->getPath();
    }
}