<?php

class QrCodeWriterTest extends \PHPUnit_Framework_TestCase {
    private $qrWriterMock;

    protected function setUp() {
        $this->qrWriterMock = $this->getMockBuilder(\BaconQrCode\Writer::class)
            ->disableOriginalConstructor()
            ->setMethods(['writeFile'])
            ->getMock();
    }

    public function testUseQrWriterToWriteQrCode() {
        $qrCodeWriter = new Services\QrCodeWriter($this->qrWriterMock, 'directory');
        
        $reservation = new QrCodeWriterTestReservationStub('unique');

        $this->qrWriterMock
            ->expects($this->once())
            ->method('writeFile')
            ->with($this->equalTo('unique'), $this->equalTo('directory/unique.png'));
        $qrCodeWriter->write($reservation, [], 'en');
    }
}

class QrCodeWriterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}