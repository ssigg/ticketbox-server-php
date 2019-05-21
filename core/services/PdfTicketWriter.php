<?php

namespace Services;

interface PdfTicketWriterInterface {
    function write(array $reservations, $printOrderId, $locale);
}

class PdfTicketWriter implements PdfTicketWriterInterface {
    private $ticketPartWriters;
    private $htmlToPdfTicketConverter;

    public function __construct(array $ticketPartWriters, HtmlToPdfTicketConverterInterface $htmlToPdfTicketConverter) {
        $this->ticketPartWriters = $ticketPartWriters;
        $this->htmlToPdfTicketConverter = $htmlToPdfTicketConverter;
    }

    public function write(array $reservations, $printOrderId, $locale) {
        $htmlFilePaths = [];
        foreach ($reservations as $reservation) {
            $htmlFilePaths[] = $this->writeHtmlForOneReservation($reservation, $printOrderId, $locale);
        }
        $pdfFilePaths = $this->htmlToPdfTicketConverter->convert($htmlFilePaths);
        return $pdfFilePaths;
    }

    private function writeHtmlForOneReservation(ExpandedReservationInterface $reservation, $printOrderId, $locale) {
        $partFilePaths = [];
        foreach ($this->ticketPartWriters as $ticketPartWriter) {
            $partFilePaths = $ticketPartWriter->write($reservation, $partFilePaths, $printOrderId, $locale);
        }
        return $partFilePaths['html'];
    }
}