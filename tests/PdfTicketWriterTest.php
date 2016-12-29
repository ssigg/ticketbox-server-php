<?php

class PdfTicketWriterTest extends \PHPUnit_Framework_TestCase {
    private $pdfPath;
    private $unique_id;
    private $reservation;

    protected function setUp() {
        $this->pdfPath = 'path/to/pdf.pdf';

        $this->ticketPartWriterMock = $this->getMockBuilder(Services\TicketPartWriterInterface::class)
            ->setMethods(['write'])
            ->getMockForAbstractClass();
        $this->ticketPartWriterMock
            ->method('write')
            ->willReturn([ 'pdf' => $this->pdfPath ]);
        $this->writer = new Services\PdfTicketWriter([ $this->ticketPartWriterMock ]);

        $this->unique_id = 'unique';
        $this->reservation = new PdfTicketWriterTestReservationStub($this->unique_id);
    }

    public function testUseTicketPartWriter() {
        $locale = 'en';
        $this->ticketPartWriterMock
            ->expects($this->once())
            ->method('write')
            ->with($this->equalTo($this->reservation), $this->equalTo([]), $this->equalTo($locale));

        $this->writer->write($this->reservation, $locale);
    }

    public function testReturnPdfFilePath() {
        $result = $this->writer->write($this->reservation, 'en');
        $this->assertSame($this->pdfPath, $result);
    }
}

class PdfTicketWriterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}
