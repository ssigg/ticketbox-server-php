<?php

class HtmlToPdfTicketConverterTest extends \PHPUnit_Framework_TestCase {
    private $postResponseMock;
    private $outputDirectory;
    private $settings;
    private $unique_id;
    private $reservation;
    private $partFilePaths;

    protected function setUp() {
        $this->postResponseMock = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)
            ->setMethods(['getBody'])
            ->getMockForAbstractClass();

        $this->postClientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['post'])
            ->getMockForAbstractClass();
        $this->postClientMock
            ->method('post')
            ->willReturn($this->postResponseMock);

        $this->getClientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['read'])
            ->getMockForAbstractClass();
        
        $this->outputDirectory = 'output';
        $this->settings = [ 'postUrl' => 'postUrl' ];
        $this->converter = new Services\HtmlToPdfTicketConverter($this->getClientMock, $this->postClientMock, $this->filePersisterMock, $this->outputDirectory, $this->settings);

        $this->unique_id = 'unique';
        $this->reservation = new HtmlToPdfTicketConverterTestReservationStub($this->unique_id);

        $this->partFilePaths = [ 'qr' => 'qrdataurl', 'html' => 'ticket.html' ];
    }

    public function testPostDataToApi() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        $expectedPostPayload = [
            'json' => [
                'html' => 'htmlToRender',
                'fileName' => $this->unique_id . '_ticket.pdf'
            ]
        ];
        $this->postClientMock
            ->expects($this->once())
            ->method('post')
            ->with($this->equalTo($this->settings['postUrl']), $this->equalTo($expectedPostPayload));
        
        $this->postResponseMock
            ->method('getBody')
            ->willReturn('{ "pdf": "PdfUrl" }');
        
        $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');
    }

    public function testGetCreatedPdf() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        $this->postResponseMock
            ->method('getBody')
            ->willReturn('{ "pdf": "PdfUrl" }');
        
        $pdfFilePath = $this->outputDirectory . '/' . $this->unique_id . '_ticket.pdf';
        $this->getClientMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('PdfUrl'), $this->equalTo([ 'sink' => $pdfFilePath ]));
        
        $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');
    }

    public function testAddPdfPathToFilePaths() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        $this->postResponseMock
            ->method('getBody')
            ->willReturn('{ "pdf": "PdfUrl" }');
        
        $partFilePaths = $this->converter->write($this->reservation, $this->partFilePaths, false, 'en');

        $pdfFilePath = $this->outputDirectory . '/' . $this->unique_id . '_ticket.pdf';
        $expectedPartFilePaths = [ 'qr' => 'qrdataurl', 'html' => 'ticket.html', 'pdf' => $pdfFilePath ];
        $this->assertSame($expectedPartFilePaths, $partFilePaths);
    }
}

class HtmlToPdfTicketConverterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}
