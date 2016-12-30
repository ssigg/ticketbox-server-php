<?php

namespace Services;

interface FilePersisterInterface {
    function read($filePath);
    function write($filePath, $content);
    function writePng($filePath, $content);
    function delete($filePath);
    function exists($filePath);
}

class FilePersister implements FilePersisterInterface {
    public function read($filePath) {
        return file_get_contents($filePath);
    }

    public function write($filePath, $content) {
        file_put_contents($filePath, $content);
    }

    public function writePng($filePath, $content) {
        imagepng($content, $filePath);
    }

    public function delete($filePath) {
        unlink($filePath);
    }

    public function exists($filePath) {
        return is_file($filePath);
    }
}