<?php

namespace Services;

interface HtmlToPdfTicketConverterInterface {
    function convert(array $htmlFilePaths);
}

class HtmlToPdfTicketConverter implements HtmlToPdfTicketConverterInterface {
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

    public function convert(array $htmlFilePaths) {
        $pdfFilePaths = [];
        foreach($htmlFilePaths as $htmlFilePath) {
            $pdfFilePaths[] = $this->convertOneFile($htmlFilePath);
        }
        return $pdfFilePaths;
    }

    private function convertOneFile($htmlFilePath) {
        $htmlFilePathParts = pathinfo($htmlFilePath);
        $pdfFileName = $htmlFilePathParts['filename'] . '.pdf';

        $postResponse = $this->postClient->post($this->postUrl, [ 
            'json' => [
                'html' => $this->filePersister->read($htmlFilePath),
                'fileName' => $pdfFileName
            ]
        ]);

        $pdfUrl = json_decode((string)$postResponse->getBody(), true)['pdf'];
        $pdfFilePath = $this->outputDirectoryPath . '/' . $pdfFileName;
        $this->getClient->get($pdfUrl, [ 'sink' => $pdfFilePath ]);
        
        return $pdfFilePath;
    }
}