<?php

namespace Services;

class PathConverter {
    private $root;

    public function __construct($root) {
        $this->root = $root;
    }

    public function convert($relativePath) {
        return $this->root . '/' . $relativePath;
    }
}