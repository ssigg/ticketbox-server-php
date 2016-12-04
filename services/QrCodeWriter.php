<?php

namespace Services;

interface QrCodeWriterInterface {
    function write($reservation);
}

class QrCodeWriter implements QRCodeWriterInterface {
    private $writer;
    private $directory;

    public function __construct(\BaconQrCode\Writer $writer, $directory) {
        $this->writer = $writer;
        $this->directory = $directory;
    }

    public function write($reservation) {
        $string = $reservation->unique_id;
        $filePath = $this->directory . '/' . $string . '.png';
        $this->writer->writeFile($string, $filePath);
        return $filePath;
    }
}