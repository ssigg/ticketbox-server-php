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
    private $engine;
    private $mailer;
    private $pdfTicketWriter;
    private $settings;

    public function __construct(Engine $engine, IMailer $mailer, PdfTicketWriterInterface $pdfTicketWriter, $settings) {
        $this->engine = $engine;
        $this->mailer = $mailer;
        $this->pdfTicketWriter = $pdfTicketWriter;
        $this->settings = $settings;
    }

    public function sendOrderConfirmation($title, $firstname, $lastname, $email, $locale, $reservations, $totalPrice) {
        $params = [
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];

        $template = __DIR__ . '/../customer/config/OrderConfirmation_' . $locale . '.txt';
        if (!is_file($template)) {
            $template = __DIR__ . '/../customer/config/OrderConfirmation_default.txt';
        }
        $body = $this->engine->renderToString($template, $params);

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
        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];

        $body = $this->engine->renderToString(__DIR__ . '/../customer/config/OrderNotification.txt', $params);

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

        $template = __DIR__ . '/../boxoffice/config/BoxofficePurchaseConfirmation' . $locale . '.txt';
        if (!is_file($template)) {
            $template = __DIR__ . '/../boxoffice/config/BoxofficePurchaseConfirmation_default.txt';
        }
        $body = $this->engine->renderToString($template, $params);

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
        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];

        $body = $this->engine->renderToString(__DIR__ . '/../boxoffice/config/BoxofficePurchaseNotification.txt', $params);

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