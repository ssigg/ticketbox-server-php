<?php

namespace Services;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Latte\Engine;

interface MailInterface {
    function sendOrderConfirmation($title, $firstname, $lastname, $email, $locale, $reservations, $totalPrice);
    function sendOrderNotification($firstname, $lastname, $email, $reservations, $totalPrice);
    function sendBoxofficePurchaseConfirmation($boxoffice, $email, $locale, $reservations, $totalPrice);
    function sendBoxofficePurchaseNotification($boxoffice, $reservations, $totalPrice);
}

class Mail implements MailInterface {
    private $twig;
    private $templateProvider;
    private $mailer;
    private $pdfTicketWriter;
    private $settings;

    public function __construct(\Twig_Environment $twig, TemplateProviderInterface $templateProvider, IMailer $mailer, PdfTicketWriterInterface $pdfTicketWriter, $settings) {
        $this->twig = $twig;
        $this->templateProvider = $templateProvider;
        $this->mailer = $mailer;
        $this->pdfTicketWriter = $pdfTicketWriter;
        $this->settings = $settings;
    }

    public function sendOrderConfirmation($title, $firstname, $lastname, $email, $locale, $reservations, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('order-confirmation', $locale, 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        $message = new Message;
        $message
            ->setFrom($this->settings['from'])
            ->setSubject($this->settings['confirmation']['subject'])
            ->addReplyTo($this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>')
            ->addTo($email)
            ->setBody($body);
        $this->mailer->send($message);
    }

    public function sendOrderNotification($firstname, $lastname, $email, $reservations, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('order-notification', 'default', 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        foreach ($this->settings['notification']['listeners'] as $listener) {
            $message = new Message;
            $message
                ->setFrom($this->settings['from'])
                ->setSubject($this->settings['notification']['subject'])
                ->addReplyTo($firstname . ' ' . $lastname . ' <' . $email . '>')
                ->addTo($listener)
                ->setBody($body);
            $this->mailer->send($message);
        }
    }

    function sendBoxofficePurchaseConfirmation($boxoffice, $email, $locale, $reservations, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('boxoffice-purchase-confirmation', $locale, 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $pdfFilePaths = [];
        foreach ($reservations as $reservation) {
            $pdfFilePath = $this->pdfTicketWriter->write($reservation, $locale);
            $pdfFilePaths[] = $pdfFilePath;
        }

        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'pdfFilePaths' => $pdfFilePaths,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        $message = new Message;
        $message
            ->setFrom($this->settings['from'])
            ->setSubject(sprintf($this->settings['confirmation']['subject'], $boxoffice))
            ->addReplyTo($this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>')
            ->addTo($email)
            ->setBody($body);
        $this->mailer->send($message);
    }

    function sendBoxofficePurchaseNotification($boxoffice, $reservations, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('boxoffice-purchase-notification', 'default', 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        foreach ($this->settings['notification']['listeners'] as $listener) {
            $message = new Message;
            $message
                ->setFrom($this->settings['from'])
                ->setSubject(sprintf($this->settings['notification']['subject'], $boxoffice))
                ->addTo($listener)
                ->setBody($body);
            $this->mailer->send($message);
        }
    }
}