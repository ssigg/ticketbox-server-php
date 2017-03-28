<?php

class SeatplanWriterTest extends \PHPUnit_Framework_TestCase {
    private $blockMapperMock;
    private $filePersisterMock;
    private $outputDirectory;
    private $writer;
    private $unique_id;
    private $reservation;
    private $validImageDataUri;

    protected function setUp() {
        $this->blockMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();

        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->setMethods(['write'])
            ->getMockForAbstractClass();

        $this->outputDirectory = 'output';
        $settings = [
            "markerColor" => [
                "R" => 255, "G" => 0, "B" => 0
            ]
        ];
        $this->writer = new Services\SeatplanWriter($this->blockMapperMock, $this->filePersisterMock, $this->outputDirectory, $settings);
        
        $this->unique_id = 'unique';
        $this->reservation = new SeatplanWriterTestReservationStub($this->unique_id, 42);

        // This is a tick mark
        $this->validImageDataUri = 'data:image/gif;base64,R0lGODdhEAAQAMwAAPj7+FmhUYjNfGuxYYDJdYTIeanOpT+DOTuANXi/bGOrWj6CONzv2sPjv2CmV1unU4zPgI/Sg6DJnJ3ImTh8Mtbs00aNP1CZSGy0YqLEn47RgXW8amasW7XWsmmvX2iuXiwAAAAAEAAQAAAFVyAgjmRpnihqGCkpDQPbGkNUOFk6DZqgHCNGg2T4QAQBoIiRSAwBE4VA4FACKgkB5NGReASFZEmxsQ0whPDi9BiACYQAInXhwOUtgCUQoORFCGt/g4QAIQA7';
    }

    public function testThrowsExceptionWhenSeatplanIsInvalid() {
        $this->blockMapperMock
            ->method('get')
            ->willReturn(new SeatplanWriterTestBlockStub('invalid'));
        $this->setExpectedException(\Exception::class);
        $this->writer->write($this->reservation, [], false, 'en');
    }

    public function testImageIsWrittenToCorrectPathWhenSeatplanIsValid() {
        $this->blockMapperMock
            ->method('get')
            ->willReturn(new SeatplanWriterTestBlockStub($this->validImageDataUri));
        
        $expectedOutputPath = $this->outputDirectory . '/' . $this->unique_id . '_seatplan.png';
        $this->filePersisterMock
            ->expects($this->once())
            ->method('writePng')
            ->with($this->equalTo($expectedOutputPath));

        $this->writer->write($this->reservation, [], false, 'en');
    }

    public function testFilePathIsAppendedToExistingFilePaths() {
        $this->blockMapperMock
            ->method('get')
            ->willReturn(new SeatplanWriterTestBlockStub($this->validImageDataUri));

        $expectedOutputPath = $this->outputDirectory . '/' . $this->unique_id . '_seatplan.png';
        $filePaths = $this->writer->write($this->reservation, [], false, 'en');
        $this->assertSame([ 'seatplan' => $expectedOutputPath ], $filePaths);
    }
}

class SeatplanWriterTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    public $seat;
    
    public function __construct($unique_id, $block_id) {
        $this->unique_id = $unique_id;
        $this->seat = new SeatplanWriterTestSeatStub($block_id);
    }
}

class SeatplanWriterTestSeatStub {
    public $block_id;
    public $x0, $x1, $x2, $x3, $y0, $y1, $y2, $y3;

    public function __construct($block_id) {
        $this->block_id = $block_id;
        $this->x0 = 0;
        $this->y0 = 0;
        $this->x1 = 1;
        $this->y1 = 1;
        $this->x2 = 2;
        $this->y2 = 2;
        $this->x3 = 3;
        $this->y3 = 3;
    }
}

class SeatplanWriterTestBlockStub {
    public $seatplan_image_data_url;

    public function __construct($seatplan_image_data_url) {
        $this->seatplan_image_data_url = $seatplan_image_data_url;
    }
}