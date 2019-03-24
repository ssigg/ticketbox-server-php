<?php

class HtmlToPdfTicketConverterTest extends \PHPUnit_Framework_TestCase {
    private $postResponseMock;
    private $outputDirectory;
    private $settings;
    private $unique_id;
    private $htmlFilePaths;

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
        $settings = [ 'postUrl' => 'postUrl' ];
        $this->converter = new Services\HtmlToPdfTicketConverter($this->getClientMock, $this->postClientMock, $this->filePersisterMock, $this->outputDirectory, $settings);

        $this->htmlFilePaths = [ 'ticket1.html', 'ticket2.html' ];
    }

    public function testPostDataToApi() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        $expectedPostPayload1 = [
            'json' => [
                'html' => 'htmlToRender',
                'fileName' => 'ticket1.pdf'
            ]
        ];
        $expectedPostPayload2 = [
            'json' => [
                'html' => 'htmlToRender',
                'fileName' => 'ticket2.pdf'
            ]
        ];
        $this->postClientMock
            ->expects($this->exactly(2))
            ->method('post')
            ->withConsecutive(
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload1) ],
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload2) ]
            );
        
        $this->postResponseMock
            ->method('getBody')
            ->willReturn('{ "pdf": "PdfUrl" }');
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testGetCreatedPdf() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        $this->postResponseMock
            ->method('getBody')
            ->willReturn('{ "pdf": "PdfUrl" }');
        
        $pdfFilePath1 = $this->outputDirectory . '/ticket1.pdf';
        $pdfFilePath2 = $this->outputDirectory . '/ticket2.pdf';
        $this->getClientMock
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [ $this->equalTo('PdfUrl'), $this->equalTo([ 'sink' => $pdfFilePath1 ]) ],
                [ $this->equalTo('PdfUrl'), $this->equalTo([ 'sink' => $pdfFilePath2 ]) ]
            );
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testAddPdfPathToFilePaths() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        $this->postResponseMock
            ->method('getBody')
            ->willReturn('{ "pdf": "PdfUrl" }');
        
        $pdfFilePaths = $this->converter->convert($this->htmlFilePaths, false, 'en');
        $expectedPdfFilePaths = [ $this->outputDirectory . '/ticket1.pdf', $this->outputDirectory . '/ticket2.pdf' ];
        $this->assertSame($expectedPdfFilePaths, $pdfFilePaths);
    }
}
