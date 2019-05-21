<?php

namespace Services;

interface TemplateProviderInterface {
    function getPath($name, $locale, $extension);
}

class TemplateProvider implements TemplateProviderInterface {
    private $filePersister;
    private $templatePath;

    public function __construct(FilePersisterInterface $filePersister, $templatePath) {
        $this->filePersister = $filePersister;
        $this->templatePath = $templatePath;
    }

    public function getPath($name, $locale, $extension) {
        $localizedName = $name . '.' . $locale . '.' . $extension;
        $localizedPath = $this->templatePath . '/' . $localizedName;
        $defaultName = $name . '.default.' . $extension;
        $defaultPath = $this->templatePath . '/' . $defaultName;
        if ($this->filePersister->exists($localizedPath)) {
            return $localizedName;
        } else if ($this->filePersister->exists($defaultPath)) {
            return $defaultName;
        } else {
            throw new \Exception('No template file found. Localized path: ' . $localizedPath . ', Default path: ' . $defaultPath);
        }
    }
}