<?php

namespace Services;

interface PdfTicketWriterInterface {
    function write($reservation, $locale);
}

class PdfTicketWriter implements PdfTicketWriterInterface {
    private $engine;
    private $dompdf;
    private $qrCodeWriter;
    private $blockMapper;
    private $filePersister;
    private $templateDirectory;
    private $tempDirectory;

    public function __construct(\Latte\Engine $engine, \Dompdf\Dompdf $dompdf, QrCodeWriterInterface $qrCodeWriter, \Spot\MapperInterface $blockMapper, FilePersisterInterface $filePersister, $templateDirectory, $tempDirectory) {
        $this->engine = $engine;
        $this->dompdf = $dompdf;
        $this->qrCodeWriter = $qrCodeWriter;
        $this->blockMapper = $blockMapper;
        $this->filePersister = $filePersister;
        $this->templateDirectory = $templateDirectory;
        $this->tempDirectory = $tempDirectory;
    }

    public function write($reservation, $locale) {
        $string = $reservation->unique_id;
        $filePath = $this->tempDirectory . '/' . $reservation->unique_id . '.pdf';
        
        $qrCodeFile = $this->qrCodeWriter->write($reservation);

        $template = $this->templateDirectory . '/PdfTicket_' . $locale . '.html';
        if (!$this->filePersister->exists($template)) {
            $template = $this->templateDirectory . '/PdfTicket_default.html';
        }

        $block = $this->blockMapper->get($reservation->seat->block_id);
        $seatplanFile = $this->tempDirectory . '/seatplan-' . $reservation->unique_id . '.png';
        $this->filePersister->write($seatplanFile, $this->filePersister->read($block->seatplan_image_data_url));
        
        $params = [
            'qrCodeFile' => $qrCodeFile,
            'reservation' => $reservation,
            'seatplanFile' => $seatplanFile
        ];

        $html = $this->engine->renderToString($template, $params);
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
        $pdfString = $this->dompdf->output();
        $this->filePersister->write($filePath, $pdfString);
        return $filePath;
    }
}