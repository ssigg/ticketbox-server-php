<?php

namespace Services;

class QrCodeWriter implements TicketPartWriterInterface {
    private $writer;
    private $outputDirectoryPath;

    public function __construct(\BaconQrCode\Writer $writer, $outputDirectoryPath) {
        $this->writer = $writer;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $printOrderId, $locale) {
        $dataUrl = 'data:image/png;base64,' . base64_encode($this->writer->writeString($reservation->unique_id));

        $partFilePaths['qr'] = $dataUrl;
        return $partFilePaths;
    }
}