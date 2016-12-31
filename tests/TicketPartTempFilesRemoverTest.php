<?php

class TicketPartTempFilesRemoverTest extends \PHPUnit_Framework_TestCase {
    private $qrPath, $seatplanPath, $htmlPath, $pdfPath;
    private $partFilePaths;
    private $filePersisterMock;
    private $remover;
    private $unique_id;
    private $reservation;

    protected function setUp() {
        $this->qrPath = 'qr-path';
        $this->seatplanPath = 'seatplan-path';
        $this->htmlPath = 'html-path';
        $this->pdfPath = 'pdf-path';
        $this->partFilePaths = [
            'qr' => $this->qrPath,
            'seatplan' => $this->seatplanPath,
            'html' => $this->htmlPath,
            'pdf' => $this->pdfPath
        ];

        $this->filePersisterMock = $this->getMockBuilder(Services\FilePersisterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMockForAbstractClass();

        $this->remover = new Services\TicketPartTempFilesRemover($this->filePersisterMock);
        
        $this->unique_id = 'unique';
        $this->reservation = new TicketPartTempFilesRemoverTestReservationStub($this->unique_id);
    }

    public function testFilePersisterIsUsedToDeleteQrAndSeatplanAndHtml() {
        $this->filePersisterMock
            ->expects($this->exactly(3))
            ->method('delete')
            ->withConsecutive(
                [$this->equalTo($this->qrPath)],
                [$this->equalTo($this->seatplanPath)],
                [$this->equalTo($this->htmlPath)]);

        $this->remover->write($this->reservation, $this->partFilePaths, 'en');
    }

    public function testQrFileIsDeleted() {
        $remainingFilePaths = $this->remover->write($this->reservation, $this->partFilePaths, 'en');
        $this->assertNotContains($this->qrPath, $remainingFilePaths);
    }

    public function testSeatplanFileIsDeleted() {
        $remainingFilePaths = $this->remover->write($this->reservation, $this->partFilePaths, 'en');
        $this->assertNotContains($this->seatplanPath, $remainingFilePaths);
    }

    public function testHtmlFileIsDeleted() {
        $remainingFilePaths = $this->remover->write($this->reservation, $this->partFilePaths, 'en');
        $this->assertNotContains($this->htmlPath, $remainingFilePaths);
    }

    public function testPdfFileIsNotDeleted() {
        $remainingFilePaths = $this->remover->write($this->reservation, $this->partFilePaths, 'en');
        $this->assertContains($this->pdfPath, $remainingFilePaths);
    }
}

class TicketPartTempFilesRemoverTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}