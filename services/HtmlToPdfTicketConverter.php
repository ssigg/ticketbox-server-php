<?php

namespace Services;

class HtmlToPdfTicketConverter implements TicketPartWriterInterface {
    private $pdfRenderer;
    private $outputDirectoryPath;

    public function __construct(\mikehaertl\wkhtmlto\Pdf $pdfRenderer, $outputDirectoryPath) {
        $this->pdfRenderer = $pdfRenderer;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $locale) {
        $this->pdfRenderer->addPage($partFilePaths['html']);
        $filePath = $this->outputDirectoryPath . '/' . $reservation->unique_id . '_ticket.pdf';
        $this->pdfRenderer->saveAs($filePath);

        $partFilePaths['pdf'] = $filePath;
        return $partFilePaths;
    }
}