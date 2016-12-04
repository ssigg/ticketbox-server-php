<?php

class PdfTicketWriterTest extends \PHPUnit_Framework_TestCase {
    private $blockMapperMock;
    private $templatMock;
    private $filePersisterMock;
    private $pdfRendererMock;
    private $qrCodeWriterMock;

    protected function setUp() {
        $this->blockMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->blockMapperMock
            ->method('get')
            ->willReturn(new PdfTicketWriterTestBlockStub('image-data-url'));

        $this->templateMock = $this->getMockBuilder(\Latte\Engine::class)
            ->setMethods(['renderToString'])
            ->getMock();

        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['read', 'write', 'exists'])
            ->getMock();

        $this->pdfRendererMock = $this->getMockBuilder(\Dompdf\Dompdf::class)
            ->setMethods(['loadHtml', 'render', 'output'])
            ->getMock();

        $this->qrCodeWriterMock = $this->getMockBuilder(Services\QrCodeWriterInterface::class)
            ->setMethods(['write'])
            ->getMock();

        $this->writer = new Services\PdfTicketWriter($this->templateMock, $this->pdfRendererMock, $this->qrCodeWriterMock, $this->blockMapperMock, $this->filePersisterMock, 'templates', 'temp');

        $this->reservation = new PdfTicketWriterTestReservationStub('unique', 42);
    }

    public function testTicketIsWrittenToPath() {
        $actualFilePath = $this->writer->write($this->reservation, 'en');
        $this->assertSame('temp/unique.pdf', $actualFilePath);
    }

    public function testQrCodeWriterIsUsedToWriteReservation() {
        $this->qrCodeWriterMock
            ->expects($this->once())
            ->method('write')
            ->with($this->equalTo($this->reservation));
        $this->writer->write($this->reservation, 'en');
    }

    public function testBlockIsFetchedFromBlockMapper() {
        $this->blockMapperMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(42));
        $this->writer->write($this->reservation, 'en');
    }

    public function testFilePersisterIsUsedOnceToRead() {
        $this->filePersisterMock
            ->expects($this->once())
            ->method('read')
            ->with($this->equalTo('image-data-url'));
        $this->writer->write($this->reservation, 'en');
    }

    public function testFilePersisterIsUsedTwiceToWrite() {
        $this->filePersisterMock
            ->expects($this->exactly(2))
            ->method('write');
        $this->writer->write($this->reservation, 'en');
    }

    public function testFilePersisterIsUsedOnceToCheckIfAFileExists() {
        $this->filePersisterMock
            ->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('templates/PdfTicket_en.html'));
        $this->writer->write($this->reservation, 'en');
    }

    public function testTemplateEngineIsUsedToRenderThePdfTemplate() {
        $this->qrCodeWriterMock
            ->method('write')
            ->willReturn('qrCodeFilePath.png');

        $expectedParams = [
            'qrCodeFile' => 'qrCodeFilePath.png',
            'reservation' => $this->reservation,
            'seatplanFile' => 'temp/seatplan-unique.png'
        ];

        $this->templateMock
            ->expects($this->once())
            ->method('renderToString')
            ->with($this->equalTo('templates/PdfTicket_default.html'), $this->equalTo($expectedParams));
        $this->writer->write($this->reservation, 'en');
    }

    public function testPdfRendererIsUsedToLoadTheGeneratedHtml() {
        $this->templateMock
            ->method('renderToString')
            ->willReturn('html-string');

        $this->pdfRendererMock
            ->expects($this->once())
            ->method('loadHtml')
            ->with($this->equalTo('html-string'));

        $this->writer->write($this->reservation, 'en');
    }

    public function testPdfRendererIsUsedToRender() {
        $this->pdfRendererMock
            ->expects($this->once())
            ->method('render');

        $this->writer->write($this->reservation, 'en');
    }

    public function testPdfRendererIsUsedToOutputGeneratedPdf() {
        $this->pdfRendererMock
            ->expects($this->once())
            ->method('output');

        $this->writer->write($this->reservation, 'en');
    }
}

class PdfTicketWriterTestReservationStub {
    public $unique_id;
    public $seat;
    
    public function __construct($unique_id, $block_id) {
        $this->unique_id = $unique_id;
        $this->seat = new PdfTicketWriterTestSeatStub($block_id);
    }
}

class PdfTicketWriterTestSeatStub {
    public $block_id;

    public function __construct($block_id) {
        $this->block_id = $block_id;
    }
}

class PdfTicketWriterTestBlockStub {
    public $seatplan_image_data_url;

    public function __construct($seatplan_image_data_url) {
        $this->seatplan_image_data_url = $seatplan_image_data_url;
    }
}