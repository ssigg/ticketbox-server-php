<?php

namespace Services;

class HtmlToPdfTicketConverter implements TicketPartWriterInterface {
    private $getClient;
    private $postClient;
    private $filePersister;
    private $outputDirectoryPath;
    private $postUrl;

    public function __construct(\GuzzleHttp\Client $getClient, \GuzzleHttp\Client $postClient, FilePersisterInterface $filePersister, $outputDirectoryPath, $settings) {
        $this->getClient = $getClient;
        $this->postClient = $postClient;
        $this->filePersister = $filePersister;
        $this->outputDirectoryPath = $outputDirectoryPath;
        $this->postUrl = $settings['postUrl'];
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $printOrderId, $locale) {
        $postResponse = $this->postClient->post($this->postUrl, [ 
            'json' => [
                'html' => $this->filePersister->read($partFilePaths['html']),
                'fileName' => $reservation->unique_id . '_ticket.pdf'
            ]
        ]);

        $pdfUrl = json_decode((string)$postResponse->getBody(), true)['pdf'];
        $filePath = $this->outputDirectoryPath . '/' . $reservation->unique_id . '_ticket.pdf';
        $pdf = $this->getClient->get($pdfUrl, [ 'sink' => $filePath ]);

        $partFilePaths['pdf'] = $filePath;
        return $partFilePaths;
    }
}