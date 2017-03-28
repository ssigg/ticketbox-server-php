<?php

namespace Services;

interface PdfTicketWriterInterface {
    function write(ExpandedReservationInterface $reservation, $printOrderId, $locale);
}

class PdfTicketWriter implements PdfTicketWriterInterface {
    private $ticketPartWriters;

    public function __construct(array $ticketPartWriters) {
        $this->ticketPartWriters = $ticketPartWriters;
    }

    public function write(ExpandedReservationInterface $reservation, $printOrderId, $locale) {
        $partFilePaths = [];
        foreach ($this->ticketPartWriters as $ticketPartWriter) {
            $partFilePaths = $ticketPartWriter->write($reservation, $partFilePaths, $printOrderId, $locale);
        }
        return $partFilePaths['pdf'];
    }
}