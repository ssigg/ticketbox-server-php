<?php

class HtmlToPdfTicketConverterTest extends \PHPUnit_Framework_TestCase {
    private $wkhtmltopdfMock;
    private $outputDirectory;
    private $unique_id;
    private $reservation;
    private $partFilePaths;

    protected function setUp() {
        $this->wkhtmltopdfMock = $this->getMockBuilder(\mikehaertl\wkhtmlto\Pdf::class)
            ->setMethods(['addPage', 'saveAs'])
            ->getMockForAbstractClass();

        $this->outputDirectory = 'output';

        $this->converter = new Services\HtmlToPdfTicketConverter($this->wkhtmltopdfMock, $this->outputDirectory);

        $this->unique_id = 'unique';
        $this->reservation = new HtmlToPdfTicketConverterTestReservationStub($this->unique_id);

        $this->partFilePaths = [ 'qr' => 'qr.png', 'seatplan' => 'seatplan.png', 'html' => 'ticket.html' ];
    }

    public function testAddGivenHtmlAsPage() {
        $this->wkhtmltopdfMock
            ->expects($this->once())
            ->method('addPage')
            ->with($this->equalTo($this->partFilePaths['html']));

        $this->converter->write($this->reservation, $this->partFilePaths, 'en');
    }

    public function testResultIsWrittenToTheCorrectLocation() {
        $expectedPath = $this->outputDirectory . '/' . $this->unique_id . '_ticket.pdf';
        $this->wkhtmltopdfMock
            ->expects($this->once())
            ->method('saveAs')
            ->with($this->equalTo($expectedPath));

        $this->converter->write($this->reservation, $this->partFilePaths, 'en');
    }

    public function testFilePathIsAppendedToExistingFilePaths() {
        $partFilePaths = $this->converter->write($this->reservation, $this->partFilePaths, 'en');
        $expectedPartFilePaths = $this->partFilePaths;
        $expectedPartFilePaths['pdf'] = $this->outputDirectory . '/' . $this->unique_id . '_ticket.pdf';
        $this->assertSame($expectedPartFilePaths, $partFilePaths);
    }
}

class HtmlToPdfTicketConverterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}
