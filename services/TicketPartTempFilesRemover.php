<?php

namespace Services;

class TicketPartTempFilesRemover implements TicketPartWriterInterface {
    private $filePersister;

    public function __construct(FilePersisterInterface $filePersister) {
        $this->filePersister = $filePersister;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $printOrderId, $locale) {
        $filePathKeysToBeRemoved = [ 'qr', 'seatplan', 'html' ];

        $remainingFilePaths = [];
        foreach ($partFilePaths as $key => $partFilePath) {
            if (in_array($key, $filePathKeysToBeRemoved)) {
                $this->filePersister->delete($partFilePath);
            } else {
                $remainingFilePaths[$key] = $partFilePath;
            }
        }
        return $remainingFilePaths;
    }
}