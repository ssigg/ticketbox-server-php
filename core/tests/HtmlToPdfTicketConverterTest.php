<?php

class HtmlToPdfTicketConverterTest extends \PHPUnit_Framework_TestCase {
    private $postResponseMock;
    private $outputDirectory;
    private $settings;
    private $unique_id;
    private $htmlFilePaths;

    protected function setUp() {
        $this->postClientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['postAsync'])
            ->getMockForAbstractClass();

        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->resolve(new \GuzzleHttp\Psr7\Response(200, [], '{ "pdf": "PdfUrl" }'));
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);

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

    public function testPostDataToApiSuccessfully() {
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
            ->method('postAsync')
            ->withConsecutive(
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload1) ],
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload2) ]
            );
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testGetCreatedPdf() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        
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
        
        $pdfFilePaths = $this->converter->convert($this->htmlFilePaths, false, 'en');
        $expectedPdfFilePaths = [ $this->outputDirectory . '/ticket1.pdf', $this->outputDirectory . '/ticket2.pdf' ];
        $this->assertSame($expectedPdfFilePaths, $pdfFilePaths);
    }
}
