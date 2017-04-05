<?php

class HtmlToPdfTicketConverterTest extends \PHPUnit_Framework_TestCase {
    private $wkhtmltopdfMock;
    private $pdfRendererFactoryMock;
    private $outputDirectory;
    private $unique_id;
    private $reservation;
    private $partFilePaths;

    protected function setUp() {
        $this->wkhtmltopdfMock = $this->getMockBuilder(\mikehaertl\wkhtmlto\Pdf::class)
            ->setMethods(['addPage', 'saveAs'])
            ->getMockForAbstractClass();

        $this->pdfRendererFactoryMock = $this->getMockBuilder(Services\PdfRendererFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->pdfRendererFactoryMock
            ->method('create')
            ->willReturn($this->wkhtmltopdfMock);

        $this->outputDirectory = 'output';

        $this->converter = new Services\HtmlToPdfTicketConverter($this->pdfRendererFactoryMock, $this->outputDirectory);

        $this->unique_id = 'unique';
        $this->reservation = new HtmlToPdfTicketConverterTestReservationStub($this->unique_id);

        $this->partFilePaths = [ 'qr' => 'qr.png', 'seatplan' => 'seatplan.png', 'html' => 'ticket.html' ];
    }

    public function testUsePdfRendererFactoryToCreatePdfRenderer() {
        $this->pdfRendererFactoryMock
            ->expects($this->once())
            ->method('create');
        
        $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');
    }

    public function testAddGivenHtmlAsPage() {
        $this->wkhtmltopdfMock
            ->expects($this->once())
            ->method('addPage')
            ->with($this->equalTo($this->partFilePaths['html']));

        $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');
    }

    public function testResultIsWrittenToTheCorrectLocation() {
        $expectedPath = $this->outputDirectory . '/' . $this->unique_id . '_ticket.pdf';
        $this->wkhtmltopdfMock
            ->expects($this->once())
            ->method('saveAs')
            ->with($this->equalTo($expectedPath));

        $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');
    }

    public function testFilePathIsAppendedToExistingFilePaths() {
        $partFilePaths = $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');
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
