<?php

namespace Services;

class QrCodeWriter implements TicketPartWriterInterface {
    private $writer;

    public function __construct(\BaconQrCode\Writer $writer) {
        $this->writer = $writer;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $printOrderId, $locale) {
        $dataUrl = 'data:image/png;base64,' . base64_encode($this->writer->writeString($reservation->unique_id));

        $partFilePaths['qr'] = $dataUrl;
        return $partFilePaths;
    }
}