<?php

class HtmlToPdfTicketConverterTest extends \PHPUnit_Framework_TestCase {
    private $postClientMock;
    private $getClientMock;
    private $filePersisterMock;
    private $logMock;
    private $outputDirectory;
    private $converter;
    private $htmlFilePaths;

    protected function setUp() {
        $this->postClientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['postAsync'])
            ->getMockForAbstractClass();

        $this->getClientMock = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['read'])
            ->getMockForAbstractClass();

        $this->logMock = $this->getMockBuilder(Services\LogInterface::class)
            ->setMethods(['error'])
            ->getMockForAbstractClass();
        
        $this->outputDirectory = 'output';
        $settings = [ 'postUrl' => 'postUrl' ];
        $this->converter = new Services\HtmlToPdfTicketConverter($this->getClientMock, $this->postClientMock, $this->filePersisterMock, $this->logMock, $this->outputDirectory, $settings);

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

        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->resolve(new \GuzzleHttp\Psr7\Response(200, [], '{ "pdf": "PdfUrl" }'));
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);

        $this->postClientMock
            ->expects($this->exactly(2))
            ->method('postAsync')
            ->withConsecutive(
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload1) ],
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload2) ]
            );
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testPostDataToApiWith401Error() {
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

        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->resolve(new \GuzzleHttp\Psr7\Response(401, [], '{ "foo": "bar" }'));
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);

        $this->postClientMock
            ->expects($this->exactly(2))
            ->method('postAsync')
            ->withConsecutive(
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload1) ],
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload2) ]
            );

        $this->logMock
            ->expects($this->exactly(1))
            ->method('error')
            ->with('Error(s) during Html to Pdf conversion: {"foo":"bar"}, {"foo":"bar"}');
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testPostDataToApiWithPromiseRejection() {
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

        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->reject('Foo! Bar!');
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);

        $this->postClientMock
            ->expects($this->exactly(2))
            ->method('postAsync')
            ->withConsecutive(
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload1) ],
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload2) ]
            );

        $this->logMock
            ->expects($this->exactly(1))
            ->method('error')
            ->with('Error(s) during Html to Pdf conversion: Foo! Bar!, Foo! Bar!');
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testPostDataToApiWithException() {
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
            ->method('postAsync')
            ->will($this->throwException(new \GuzzleHttp\Exception\TransferException('Exception!')));

        $this->postClientMock
            ->expects($this->exactly(2))
            ->method('postAsync')
            ->withConsecutive(
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload1) ],
                [ $this->equalTo('postUrl'), $this->equalTo($expectedPostPayload2) ]
            );

        $this->logMock
            ->expects($this->exactly(3))
            ->method('error')
            ->withConsecutive(
                [ $this->equalTo('Error(s) during Html to Pdf conversion: Exception!') ],
                [ $this->equalTo('Error(s) during Html to Pdf conversion: Exception!') ],
                [ $this->equalTo('Error(s) during Html to Pdf conversion: Unknown Error, Unknown Error') ]);
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testGetCreatedPdfSuccessfully() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');

        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->resolve(new \GuzzleHttp\Psr7\Response(200, [], '{ "pdf": "PdfUrl" }'));
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);
        
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

    public function testGetCreatedPdfWithException() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');

        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->resolve(new \GuzzleHttp\Psr7\Response(200, [], '{ "pdf": "PdfUrl" }'));
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);
        
        $pdfFilePath1 = $this->outputDirectory . '/ticket1.pdf';
        $pdfFilePath2 = $this->outputDirectory . '/ticket2.pdf';
        $this->getClientMock
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [ $this->equalTo('PdfUrl'), $this->equalTo([ 'sink' => $pdfFilePath1 ]) ],
                [ $this->equalTo('PdfUrl'), $this->equalTo([ 'sink' => $pdfFilePath2 ]) ]
            );
        $this->getClientMock
            ->method('get')
            ->will($this->throwException(new \GuzzleHttp\Exception\TransferException('Exception!')));

        $this->logMock
            ->expects($this->exactly(2))
            ->method('error')
            ->with('Error(s) during Html to Pdf conversion: Exception!');
        
        $this->converter->convert($this->htmlFilePaths, false, 'en');
    }

    public function testAddPdfPathToFilePaths() {
        $this->filePersisterMock
            ->method('read')
            ->willReturn('htmlToRender');
        
        // See https://gist.github.com/lubomir-haralampiev/890f5778f8e71e597329f471e5ce8556
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise) {
            $promise->resolve(new \GuzzleHttp\Psr7\Response(200, [], '{ "pdf": "PdfUrl" }'));
        });
        $this->postClientMock
            ->method('postAsync')
            ->willReturn($promise);
        
        $pdfFilePaths = $this->converter->convert($this->htmlFilePaths, false, 'en');
        $expectedPdfFilePaths = [ $this->outputDirectory . '/ticket1.pdf', $this->outputDirectory . '/ticket2.pdf' ];
        $this->assertSame($expectedPdfFilePaths, $pdfFilePaths);
    }
}
