<?php

namespace Services;

use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Latte\Engine;

interface MailInterface {
    function sendOrderConfirmation($title, $firstname, $lastname, $email, $reservations, $totalPrice);
    function sendOrderNotification($firstname, $lastname, $email, $reservations, $totalPrice);
    function sendBoxofficePurchaseNotification($boxoffice, $reservations, $totalPrice);
}

class Mail implements MailInterface {
    private $engine;
    private $mailer;
    private $settings;

    public function __construct(Engine $engine, IMailer $mailer, $settings) {
        $this->engine = $engine;
        $this->mailer = $mailer;
        $this->settings = $settings;
    }

    public function sendOrderConfirmation($title, $firstname, $lastname, $email, $reservations, $totalPrice) {
        $params = [
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];

        $template = __DIR__ . '/../customer/config/OrderConfirmation.txt';
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
                ->setSubject($this->settings['notification']['subject'])
                ->addTo($listener)
                ->setBody($body);
            $this->mailer->send($message);
        }
    }
}