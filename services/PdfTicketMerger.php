<?php

namespace Services;

interface PdfTicketMergerInterface {
    function merge($pdfFilePaths, $outputFileName);
}

class PdfTicketMerger implements PdfTicketMergerInterface {
    private $outputDirectory;

    public function __construct($outputDirectory) {
        $this->outputDirectory = $outputDirectory;
    }

    public function merge($pdfFilePaths, $outputFileName) {
        $pdf = new \Jurosh\PDFMerge\PDFMerger;
        foreach ($pdfFilePaths as $pdfFilePath) {
            $pdf->addPDF($pdfFilePath, 'all');
        }
        $pdf->merge('file', $this->outputDirectory . '/' . $outputFileName);
    }
}