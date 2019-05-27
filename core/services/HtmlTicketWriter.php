<?php

namespace Services;

class HtmlTicketWriter implements TicketPartWriterInterface {
    private $twig;
    private $templateProvider;
    private $filePersister;
    private $templatePath;
    private $outputDirectoryPath;

    public function __construct(\Twig_Environment $twig, TemplateProviderInterface $templateProvider, FilePersisterInterface $filePersister, $templatePath, $outputDirectoryPath) {
        $this->twig = $twig;
        $this->templateProvider = $templateProvider;
        $this->filePersister = $filePersister;
        $this->templatePath = $templatePath;
        $this->outputDirectoryPath = $outputDirectoryPath;
    }

    public function write(ExpandedReservationInterface $reservation, array $partFilePaths, $printOrderId, $locale) {
        $templateFileName = $this->templateProvider->getFileName('ticket', $locale, 'html');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'printOrderId' => $printOrderId,
            'template_path' => $this->templatePath,
            'reservation' => $reservation,
            'qr' => $partFilePaths['qr']
        ];
        $result = $template->render($params);

        $filePath = $this->outputDirectoryPath . '/' . $reservation->unique_id . '.html';
        $this->filePersister->write($filePath, $result);
        
        $partFilePaths['html'] = $filePath;
        return $partFilePaths;
    }
}