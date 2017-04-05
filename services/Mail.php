<?php

namespace Services;

interface MailInterface {
    function sendOrderConfirmation($title, $firstname, $lastname, $email, $locale, $reservations, $totalPrice);
    function sendOrderNotification($firstname, $lastname, $email, $reservations, $totalPrice);
    function sendBoxofficePurchaseConfirmation($boxoffice, $email, $locale, $reservations, $totalPrice);
    function sendBoxofficePurchaseNotification($boxoffice, $reservations, $totalPrice);
    function sendCustomerPurchaseConfirmation($purchase, $totalPrice);
    function sendCustomerPurchaseNotification($purchase, $totalPrice);
}

class Mail implements MailInterface {
    private $twig;
    private $templateProvider;
    private $messageFactory;
    private $mailer;
    private $pdfTicketWriter;
    private $log;
    private $settings;

    public function __construct(
        \Twig_Environment $twig,
        TemplateProviderInterface $templateProvider,
        MessageFactoryInterface $messageFactory,
        \Nette\Mail\IMailer $mailer,
        PdfTicketWriterInterface $pdfTicketWriter,
        LogInterface $log,
        $settings) {
        $this->twig = $twig;
        $this->templateProvider = $templateProvider;
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
        $this->pdfTicketWriter = $pdfTicketWriter;
        $this->log = $log;
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

        $from = $this->settings['from'];
        $to = $email;
        $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
        $subject = $this->settings['order-confirmation']['subject'];
        $attachments = [];
        $message = $this->messageFactory->create($from, $to, $replyTo, $subject, $body, $attachments);
        
        try {
            $this->mailer->send($message);
            $this->log->info('Sent order confirmation mail to ' . $to);
        } catch (\Nette\Mail\SendException $e) {
            $this->log->error($e);
        }
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

        foreach ($this->settings['order-notification']['listeners'] as $listener) {
            $from = $this->settings['from'];
            $to = $listener;
            $replyTo = $firstname . ' ' . $lastname . ' <' . $email . '>';
            $subject = $this->settings['order-notification']['subject'];
            $attachments = [];
            $message = $this->messageFactory->create($from, $to, $replyTo, $subject, $body, $attachments);

            try {
                $this->mailer->send($message);
                $this->log->info('Sent order notification mail to ' . $to);
            } catch (\Nette\Mail\SendException $e) {
                $this->log->error($e);
            }
        }
    }

    public function sendBoxofficePurchaseConfirmation($boxoffice, $email, $locale, $reservations, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('boxoffice-purchase-confirmation', $locale, 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $pdfFilePaths = [];
        foreach ($reservations as $reservation) {
            $pdfFilePath = $this->pdfTicketWriter->write($reservation, false, $locale);
            $pdfFilePaths[] = $pdfFilePath;
        }

        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'pdfFilePaths' => $pdfFilePaths,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        $from = $this->settings['from'];
        $to = $email;
        $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
        $subject = sprintf($this->settings['confirmation']['subject'], $boxoffice);
        $attachments = $pdfFilePaths;
        $message = $this->messageFactory->create($from, $to, $replyTo, $subject, $body, $attachments);

        try {
            $this->mailer->send($message);
            $this->log->info('Sent boxoffice purchase confirmation mail to ' . $to);
        } catch (\Nette\Mail\SendException $e) {
            $this->log->error($e);
        }
    }

    public function sendBoxofficePurchaseNotification($boxoffice, $reservations, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('boxoffice-purchase-notification', 'default', 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        foreach ($this->settings['notification']['listeners'] as $listener) {
            $from = $this->settings['from'];
            $to = $listener;
            $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
            $subject = sprintf($this->settings['notification']['subject'], $boxoffice);
            $attachments = [];
            $message = $this->messageFactory->create($from, $to, $replyTo, $subject, $body, $attachments);

            try {
                $this->mailer->send($message);
                $this->log->info('Sent boxoffice purchase notification mail to ' . $to);
            } catch (\Nette\Mail\SendException $e) {
                $this->log->error($e);
            }
        }
    }

    public function sendCustomerPurchaseConfirmation($purchase, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('customer-purchase-confirmation', $purchase->locale, 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $pdfFilePaths = [];
        foreach ($purchase->reservations as $reservation) {
            $pdfFilePath = $this->pdfTicketWriter->write($reservation, false, $purchase->locale);
            $pdfFilePaths[] = $pdfFilePath;
        }

        $params = [
            'purchase' => $purchase,
            'reservations' => $purchase->reservations,
            'pdfFilePaths' => $pdfFilePaths,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        $from = $this->settings['from'];
        $to = $purchase->email;
        $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
        $subject = $this->settings['purchase-confirmation']['subject'];
        $attachments = $pdfFilePaths;
        $message = $this->messageFactory->create($from, $to, $replyTo, $subject, $body, $attachments);

        try {
            $this->mailer->send($message);
            $this->log->info('Sent customer purchase confirmation mail to ' . $to);
        } catch (\Nette\Mail\SendException $e) {
            $this->log->error($e);
        }
    }

    public function sendCustomerPurchaseNotification($purchase, $totalPrice) {
        $templateFileName = $this->templateProvider->getPath('customer-purchase-notification', 'default', 'txt');
        $template = $this->twig->loadTemplate($templateFileName);

        $params = [
            'purchase' => $purchase,
            'reservations' => $purchase->reservations,
            'total' => $totalPrice
        ];
        $body = $template->render($params);

        foreach ($this->settings['purchase-notification']['listeners'] as $listener) {
            $from = $this->settings['from'];
            $to = $listener;
            $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
            $subject = $this->settings['purchase-notification']['subject'];
            $attachments = [];
            $message = $this->messageFactory->create($from, $to, $replyTo, $subject, $body, $attachments);

            try {
                $this->mailer->send($message);
                $this->log->info('Sent customer purchase notification mail to ' . $to);
            } catch (\Nette\Mail\SendException $e) {
                $this->log->error($e);
            }
        }
    }
}