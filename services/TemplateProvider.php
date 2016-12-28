<?php

namespace Services;

interface TemplateProviderInterface {
    function getPath($name, $locale, $extension);
}

class TemplateProvider implements TemplateProviderInterface {
    private $filePersister;
    private $templatePath;

    public function __construct(FilePersisterInterface $filePersister, string $templatePath) {
        $this->filePersister = $filePersister;
        $this->templatePath = $templatePath;
    }

    public function getPath($name, $locale, $extension) {
        $localizedPath = $this->templatePath . '/' . $name . '.' . $locale . '.' . $extension;
        $defaultPath = $this->templatePath . '/' . $name . '.default.' . $extension;
        if ($this->filePersister->exists($localizedPath)) {
            return $localizedPath;
        } else if ($this->filePersister->exists($defaultPath)) {
            return $defaultPath;
        } else {
            throw new Exception('No template file found.');
        }
    }
}