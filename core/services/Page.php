<?php

namespace Services;

interface PageInterface {
    function getTicketValidatorResultPage(TicketValidatorResult $ticketValidatorResult);
}

class Page implements PageInterface {
    private $twig;
    private $templateProvider;

    public function __construct(\Twig_Environment $twig, TemplateProviderInterface $templateProvider) {
        $this->twig = $twig;
        $this->templateProvider = $templateProvider;
    }

    public function getTicketValidatorResultPage(TicketValidatorResult $ticketValidatorResult) {
        $templateName = '';
        if ($ticketValidatorResult->status == TicketValidatorStatus::Ok) {
            $templateName = 'ticket-validation-ok';
        } else if ($ticketValidatorResult->status == TicketValidatorStatus::Warning) {
            $templateName = 'ticket-validation-warning';
        } else if ($ticketValidatorResult->status == TicketValidatorStatus::Error) {
            $templateName = 'ticket-validation-error';
        } else {
            throw new \Exception('Unknown TicketValidatorStatus: ' . $ticketValidatorResult->status);
        }
        $templateFileName = $this->templateProvider->getPath($templateName, 'default', 'html');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'messages' => $ticketValidatorResult->messages
        ];
        $body = $template->render($params);
        return $body;
    }
}