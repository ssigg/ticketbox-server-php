<?php

namespace Services;

interface PdfRendererFactoryInterface {
    function create();
}

class PdfRendererFactory implements PdfRendererFactoryInterface {
    private $pdfRendererBinary;

    public function __construct(PdfRendererBinaryInterface $pdfRendererBinary) {
        $this->pdfRendererBinary = $pdfRendererBinary;
    }

    public function create() {
        $options = [
            'binary' => $this->pdfRendererBinary->getPath()
        ];
        $pdfRenderer = new \mikehaertl\wkhtmlto\Pdf($options);
        return $pdfRenderer;
    }
}