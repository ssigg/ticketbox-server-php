<?php

namespace Services;

class QrCodeWriter implements TicketPartWriterInterface {
    private $writer;
    private $outputDirectoryPath;

    public function __construct(\BaconQrCode\Writer $writer, $outputDirectoryPath) {
        $this->writer = $writer;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $locale) {
        $filePath = $this->outputDirectoryPath . '/' . $reservation->unique_id . '_qr.png';
        $this->writer->writeFile($reservation->unique_id, $filePath);

        $partFilePaths['qr'] = $filePath;
        return $partFilePaths;
    }
}