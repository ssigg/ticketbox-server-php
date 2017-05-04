<?php

class PdfTicketMergerTest extends \PHPUnit_Framework_TestCase {
    private $sourcePdfPath = __DIR__ . '/data/foo.pdf';
    private $mergedPdfPath = __DIR__ . '/data/merged.pdf';

    protected function tearDown() {
        if (file_exists($this->mergedPdfPath)) {
            unlink($this->mergedPdfPath);
        }
    }

    public function testMergePdf() {
        $merger = new Services\PdfTicketMerger(__DIR__ . '/data');
        $merger->merge([ $this->sourcePdfPath ], 'merged.pdf');
        $this->assertFileExists($this->mergedPdfPath);
    }
}