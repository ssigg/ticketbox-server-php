<?php

namespace Services;

interface MailTemplateParserInterface {
    function parse($templatePath, $params);
}

class MailTemplateParser implements MailTemplateParserInterface {
    private $filePersister;
    private $frontMatter;
    private $fromStringTwig;

    public function __construct(FilePersisterInterface $filePersister, \Webuni\FrontMatter\FrontMatter $frontMatter, \Twig_Environment $fromStringTwig) {
        $this->filePersister = $filePersister;
        $this->frontMatter = $frontMatter;
        $this->fromStringTwig = $fromStringTwig;
    }

    public function parse($templatePath, $params) {
        $templateContent = $this->filePersister->read($templatePath);
        $document = $this->frontMatter->parse($templateContent);

        $subjectTemplate = $document->getData()['subject'];
        $bodyTemplate = $document->getContent();

        $subject = $this->fromStringTwig->render($subjectTemplate, $params);
        $body = $this->fromStringTwig->render($bodyTemplate, $params);

        return new MailContents($subject, $body);
    }
}

class MailContents {
    public $subject;
    public $body;

    public function __construct($subject, $body) {
        $this->subject = $subject;
        $this->body = $body;
    }
}