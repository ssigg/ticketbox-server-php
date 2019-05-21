<?php

class PdfTicketWriterTest extends \PHPUnit_Framework_TestCase {
    private $htmlPath;
    private $unique_id;
    private $reservation;

    protected function setUp() {
        $htmlPath = 'path/to/html.html';
        $this->pdfPath = 'path/to/html.html';

        $this->ticketPartWriterMock = $this->getMockBuilder(Services\TicketPartWriterInterface::class)
            ->setMethods(['write'])
            ->getMockForAbstractClass();
        $this->ticketPartWriterMock
            ->method('write')
            ->willReturn([ 'html' => $htmlPath ]);
        $this->htmlToPdfTicketConverterMock = $this->getMockBuilder(Services\HtmlToPdfTicketConverterInterface::class)
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
        $this->htmlToPdfTicketConverterMock
            ->method('convert')
            ->willReturn([$this->pdfPath]);
        $this->writer = new Services\PdfTicketWriter([ $this->ticketPartWriterMock ], $this->htmlToPdfTicketConverterMock);

        $this->unique_id = 'unique';
        $this->reservation = new PdfTicketWriterTestReservationStub($this->unique_id);
    }

    public function testUseTicketPartWriter() {
        $locale = 'en';
        $this->ticketPartWriterMock
            ->expects($this->once())
            ->method('write')
            ->with($this->equalTo($this->reservation), $this->equalTo([]), $this->equalTo(false), $this->equalTo($locale));

        $this->writer->write([ $this->reservation ], false, $locale);
    }

    public function testReturnPdfFilePath() {
        $result = $this->writer->write([ $this->reservation ], false, 'en');
        $this->assertSame([$this->pdfPath], $result);
    }
}

class PdfTicketWriterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}
