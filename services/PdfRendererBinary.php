<?php

namespace Services;

interface PdfRendererBinaryInterface {
    function getPath();
}

class PdfRendererBinary implements PdfRendererBinaryInterface {
    private $operatingSystem;

    public function __construct(\Tivie\OS\DetectorInterface $operatingSystem) {
        $this->operatingSystem = $operatingSystem;
    }

    public function getPath() {
        $osType = $this->operatingSystem->getType();
        $osFamily = $this->operatingSystem->getFamily();
        if ($osType == \Tivie\OS\MACOSX) {
            return __DIR__ . '/../vendor/message/wkhtmltopdf/bin/wkhtmltopdf-osx'; 
        } else if ($osFamily == \Tivie\OS\UNIX_FAMILY) {
            return __DIR__ . '/../vendor/message/wkhtmltopdf/bin/wkhtmltopdf-i386';
        } else {
            throw new \Exception('Unsupported operating system: ' . $osFamily . '/' . $osType);
        }
    }
}