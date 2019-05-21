<?php

class QrCodeWriterTest extends \PHPUnit_Framework_TestCase {
    private $qrWriterMock;
    private $outputDirectory;
    private $writer;
    private $unique_id;
    private $reservation;

    protected function setUp() {
        $this->qrWriterMock = $this->getMockBuilder(\BaconQrCode\Writer::class)
            ->disableOriginalConstructor()
            ->setMethods(['writeString'])
            ->getMock();

        $this->outputDirectory = 'directory';
        $this->writer = new Services\QrCodeWriter($this->qrWriterMock, $this->outputDirectory);
        
        $this->unique_id = 'unique';
        $this->reservation = new QrCodeWriterTestReservationStub($this->unique_id);
    }

    public function testUseQrWriterToWriteQrCode() {
        $this->qrWriterMock
            ->expects($this->once())
            ->method('writeString')
            ->with($this->equalTo($this->reservation->unique_id));
        $this->writer->write($this->reservation, [], false, 'en');
    }

    public function testFilePathIsAppendedToExistingFilePaths() {
        $this->qrWriterMock
            ->method('writeString')
            ->willReturn('QrCodeString');
        $expectedQrCodeString = 'data:image/png;base64,UXJDb2RlU3RyaW5n';
        $filePaths = $this->writer->write($this->reservation, [], false, 'en');
        $this->assertSame([ 'qr' => $expectedQrCodeString ], $filePaths);
    }
}

class QrCodeWriterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}