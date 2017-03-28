<?php

namespace Services;

class HtmlToPdfTicketConverter implements TicketPartWriterInterface {
    private $pdfRendererFactory;
    private $outputDirectoryPath;

    public function __construct(PdfRendererFactoryInterface $pdfRendererFactory, $outputDirectoryPath) {
        $this->pdfRendererFactory = $pdfRendererFactory;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $printOrderId, $locale) {
        $pdfRenderer = $this->pdfRendererFactory->create();
        $pdfRenderer->addPage($partFilePaths['html']);
        $filePath = $this->outputDirectoryPath . '/' . $reservation->unique_id . '_ticket.pdf';
        $pdfRenderer->saveAs($filePath);

        $partFilePaths['pdf'] = $filePath;
        return $partFilePaths;
    }
}