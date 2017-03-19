<?php

class HtmlTicketWriterTest extends \PHPUnit_Framework_TestCase {
    private $templateMock;
    private $twigMock;
    private $templateProviderMock;
    private $filePersisterMock;
    private $outputDirectory;
    private $writer;
    private $unique_id;
    private $reservation;
    private $partFilePaths;

    protected function setUp() {
        $this->templateMock = $this->getMockBuilder(\Twig_TemplateInterface::class)
            ->setMethods(['render'])
            ->getMockForAbstractClass();

        $this->twigMock = $this->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadTemplate'])
            ->getMockForAbstractClass();
        $this->twigMock->method('loadTemplate')->willReturn($this->templateMock);

        $this->templateProviderMock = $this->getMockBuilder(Services\TemplateProviderInterface::class)
            ->setMethods(['getPath'])
            ->getMockForAbstractClass();

        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['write'])
            ->getMockForAbstractClass();

        $this->templateDirectory = 'templates';
        $this->outputDirectory = 'output';
        $this->writer = new Services\HtmlTicketWriter($this->twigMock, $this->templateProviderMock, $this->filePersisterMock, $this->templateDirectory, $this->outputDirectory);
        
        $this->unique_id = 'unique';
        $this->reservation = new HtmlTicketWriterTestReservationStub($this->unique_id);

        $this->partFilePaths = [ 'qr' => 'qr.png', 'seatplan' => 'seatplan.png' ];
    }

    public function testUseTemplateProviderToGetTemplatePath() {
        $locale = 'en';

        $this->templateProviderMock
            ->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo('ticket'), $this->equalTo($locale), $this->equalTo('html'));
        $this->writer->write($this->reservation, $this->partFilePaths, $locale);
    }

    public function testUseTwigToLoadTheTemplate() {
        $templatePath = 'template.html';

        $this->templateProviderMock
            ->method('getPath')
            ->willReturn($templatePath);

        $this->twigMock
            ->expects($this->once())
            ->method('loadTemplate')
            ->with($this->equalTo($templatePath));
        $this->writer->write($this->reservation, $this->partFilePaths, 'en');
    }

    public function testResultIsWrittenToTheCorrectLocation() {
        $this->templateMock
            ->method('render')
            ->willReturn('result');

        $expectedOutputPath = $this->outputDirectory . '/' . $this->unique_id . '_ticket.html';
        $this->filePersisterMock
            ->expects($this->once())
            ->method('write')
            ->with($this->equalTo($expectedOutputPath), $this->equalTo('result'));
        $this->writer->write($this->reservation, $this->partFilePaths, 'en');
    }

    public function testFilePathIsAppendedToExistingFilePaths() {
        $filePaths = $this->writer->write($this->reservation, $this->partFilePaths, 'en');
        $expectedPartFilePaths = $this->partFilePaths;
        $expectedPartFilePaths['html'] = $this->outputDirectory . '/' . $this->unique_id . '_ticket.html';
        $this->assertSame($expectedPartFilePaths, $filePaths);
    }
}

class HtmlTicketWriterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}