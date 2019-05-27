<?php

namespace Services;

interface HtmlToPdfTicketConverterInterface {
    function convert(array $htmlFilePaths);
}

class HtmlToPdfTicketConverter implements HtmlToPdfTicketConverterInterface {
    private $getClient;
    private $postClient;
    private $filePersister;
    private $log;
    private $outputDirectoryPath;
    private $postUrl;

    public function __construct(\GuzzleHttp\Client $getClient, \GuzzleHttp\Client $postClient, FilePersisterInterface $filePersister, LogInterface $log, $outputDirectoryPath, $settings) {
        $this->getClient = $getClient;
        $this->postClient = $postClient;
        $this->filePersister = $filePersister;
        $this->log = $log;
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
        $errors = [];
        foreach ($results as $pdfFileName => $result) {
            if (array_key_exists('value', $result) && $result['value'] != null) {
                $body = json_decode((string)$result['value']->getBody(), true);
                if (array_key_exists('pdf', $body)) {
                    $pdfUrl = json_decode((string)$result['value']->getBody(), true)['pdf'];
                    $pdfFilePaths[] = $this->getOnePdfFile($pdfUrl, $pdfFileName);
                } else {
                    $errors[] = json_encode($body);
                }
            } else if (array_key_exists('reason', $result) && $result['reason'] != null) {
                $errors[] = $result['reason'];
            } else {
                $errors[] = 'Unknown Error';
            }
        }

        if (count($errors) > 0) {
            $this->log->error('Error(s) during Html to Pdf conversion: ' . implode(', ', $errors));
        }

        return $pdfFilePaths;
    }

    private function postOneHtmlFile($htmlFilePath, $pdfFileName) {
        try {
            $promise = $this->postClient->postAsync($this->postUrl, [ 
                'json' => [
                    'html' => $this->filePersister->read($htmlFilePath),
                    'fileName' => $pdfFileName
                ]
            ]);

            return $promise;
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $this->log->error('Error(s) during Html to Pdf conversion: ' . $e->getMessage());
        }
    }

    private function getOnePdfFile($pdfUrl, $pdfFileName) {
        $pdfFilePath = $this->outputDirectoryPath . '/' . $pdfFileName;
        try {
            $this->getClient->get($pdfUrl, [ 'sink' => $pdfFilePath ]);
            return $pdfFilePath;
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $this->log->error('Error(s) during Html to Pdf conversion: ' . $e->getMessage());
        }
    }
}