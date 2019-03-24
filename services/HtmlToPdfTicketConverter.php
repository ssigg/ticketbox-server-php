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
        $promises = [];
        foreach ($htmlFilePaths as $htmlFilePath) {
            $htmlFilePathParts = pathinfo($htmlFilePath);
            $pdfFileName = $htmlFilePathParts['filename'] . '.pdf';
            $promises[$pdfFileName] = $this->postOneHtmlFile($htmlFilePath, $pdfFileName);
        }
        
        $results = \GuzzleHttp\Promise\settle($promises)->wait();
        $pdfFilePaths = [];
        foreach ($results as $pdfFileName => $result) {
            $pdfUrl = json_decode((string)$result['value']->getBody(), true)['pdf'];
            $pdfFilePaths[] = $this->getOnePdfFile($pdfUrl, $pdfFileName);
        }

        return $pdfFilePaths;
    }

    private function postOneHtmlFile($htmlFilePath, $pdfFileName) {
        $promise = $this->postClient->postAsync($this->postUrl, [ 
            'json' => [
                'html' => $this->filePersister->read($htmlFilePath),
                'fileName' => $pdfFileName
            ]
        ]);

        return $promise;
    }

    private function getOnePdfFile($pdfUrl, $pdfFileName) {
        $pdfFilePath = $this->outputDirectoryPath . '/' . $pdfFileName;
        $this->getClient->get($pdfUrl, [ 'sink' => $pdfFilePath ]);
        return $pdfFilePath;
    }
}