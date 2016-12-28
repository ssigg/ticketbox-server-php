<?php

namespace Services;

interface HtmlTicketWriterInterface {
    function write(ExpandedReservation $reservation, string $locale);
}

class HtmlTicketWriter implements HtmlTicketWriterInterface {
    private $twig;
    private $templateProvider;
    private $filePersister;
    private $outputDirectoryPath;

    public function __construct(\Twig_Environment $twig, TemplateProviderInterface $templateProvider, FilePersisterInterface $filePersister, string $outputDirectoryPath) {
        $this->twig = $twig;
        $this->templateProvider = $templateProvider;
        $this->filePersister = $filePersister;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservation $reservation, string $locale) {
        $templateFilePath = $this->templateProvider->getPath('ticket', $locale, 'html');
        $template = $this->_twig->loadTemplate($templateFilePath);

        $params = [
            'reservation' => $reservation
        ];
        $result = $template->render($params);
        $filePath = $this->outputDirectoryPath . '/' . $reservation->unique_id . '.html';
        $this->filePersister->write($reservation->unique_id, $result);
    }
}