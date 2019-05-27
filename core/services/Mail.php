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
    private $templateProvider;
    private $mailTemplateParser;
    private $messageFactory;
    private $mailer;
    private $pdfTicketWriter;
    private $log;
    private $settings;
    private $hostName;
    private $administrator;

    public function __construct(
        TemplateProviderInterface $templateProvider,
        MailTemplateParserInterface $mailTemplateParser,
        MessageFactoryInterface $messageFactory,
        \Nette\Mail\IMailer $mailer,
        PdfTicketWriterInterface $pdfTicketWriter,
        LogInterface $log,
        $settings,
        $hostName,
        $administrator) {
        $this->templateProvider = $templateProvider;
        $this->mailTemplateParser = $mailTemplateParser;
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
        $this->pdfTicketWriter = $pdfTicketWriter;
        $this->log = $log;
        $this->settings = $settings;
        $this->hostName = $hostName;
        $this->administrator = $administrator;
    }

    public function sendOrderConfirmation($title, $firstname, $lastname, $email, $locale, $reservations, $totalPrice) {
        $templatePath = $this->templateProvider->getPath('customer-order-confirmation', $locale, 'txt');
        
        $params = [
            'title' => $title,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'reservations' => $reservations,
            'total' => $totalPrice,
            'hostName' => $this->hostName,
            'administrator' => $this->administrator
        ];

        $mailContents = $this->mailTemplateParser->parse($templatePath, $params);

        $from = $this->settings['from'];
        $to = $email;
        $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
        $attachments = [];
        $message = $this->messageFactory->create($from, $to, $replyTo, $mailContents->subject, $mailContents->body, $attachments);
        
        try {
            $this->mailer->send($message);
            $this->log->info('Sent order confirmation mail to ' . $to);
        } catch (\Nette\Mail\SendException $e) {
            $this->log->error($e);
        }
    }

    public function sendOrderNotification($firstname, $lastname, $email, $reservations, $totalPrice) {
        $templatePath = $this->templateProvider->getPath('customer-order-notification', 'default', 'txt');

        $params = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'reservations' => $reservations,
            'total' => $totalPrice,
            'hostName' => $this->hostName,
            'administrator' => $this->administrator
        ];

        $mailContents = $this->mailTemplateParser->parse($templatePath, $params);

        foreach ($this->settings['notification']['listeners'] as $listener) {
            $from = $this->settings['from'];
            $to = $listener;
            $replyTo = $firstname . ' ' . $lastname . ' <' . $email . '>';
            $attachments = [];
            $message = $this->messageFactory->create($from, $to, $replyTo, $mailContents->subject, $mailContents->body, $attachments);

            try {
                $this->mailer->send($message);
                $this->log->info('Sent order notification mail to ' . $to);
            } catch (\Nette\Mail\SendException $e) {
                $this->log->error($e);
            }
        }
    }

    public function sendBoxofficePurchaseConfirmation($boxoffice, $email, $locale, $reservations, $totalPrice) {
        $templatePath = $this->templateProvider->getPath('boxoffice-purchase-confirmation', $locale, 'txt');

        $pdfFilePaths = $this->pdfTicketWriter->write($reservations, false, $locale);

        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'pdfFilePaths' => $pdfFilePaths,
            'total' => $totalPrice,
            'hostName' => $this->hostName,
            'administrator' => $this->administrator
        ];

        $mailContents = $this->mailTemplateParser->parse($templatePath, $params);

        $from = $this->settings['from'];
        $to = $email;
        $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
        $attachments = $pdfFilePaths;
        $message = $this->messageFactory->create($from, $to, $replyTo, $mailContents->subject, $mailContents->body, $attachments);

        try {
            $this->mailer->send($message);
            $this->log->info('Sent boxoffice purchase confirmation mail to ' . $to);
        } catch (\Nette\Mail\SendException $e) {
            $this->log->error($e);
        }
    }

    public function sendBoxofficePurchaseNotification($boxoffice, $reservations, $totalPrice) {
        $templatePath = $this->templateProvider->getPath('boxoffice-purchase-notification', 'default', 'txt');

        $params = [
            'boxoffice' => $boxoffice,
            'reservations' => $reservations,
            'total' => $totalPrice,
            'hostName' => $this->hostName,
            'administrator' => $this->administrator
        ];

        $mailContents = $this->mailTemplateParser->parse($templatePath, $params);

        foreach ($this->settings['notification']['listeners'] as $listener) {
            $from = $this->settings['from'];
            $to = $listener;
            $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
            $attachments = [];
            $message = $this->messageFactory->create($from, $to, $replyTo, $mailContents->subject, $mailContents->body, $attachments);

            try {
                $this->mailer->send($message);
                $this->log->info('Sent boxoffice purchase notification mail to ' . $to);
            } catch (\Nette\Mail\SendException $e) {
                $this->log->error($e);
            }
        }
    }

    public function sendCustomerPurchaseConfirmation($purchase, $totalPrice) {
        $templatePath = $this->templateProvider->getPath('customer-purchase-confirmation', $purchase->locale, 'txt');

        $pdfFilePaths = $this->pdfTicketWriter->write($purchase->reservations, false, $purchase->locale);

        $params = [
            'purchase' => $purchase,
            'reservations' => $purchase->reservations,
            'pdfFilePaths' => $pdfFilePaths,
            'total' => $totalPrice,
            'hostName' => $this->hostName,
            'administrator' => $this->administrator
        ];
        
        $mailContents = $this->mailTemplateParser->parse($templatePath, $params);

        $from = $this->settings['from'];
        $to = $purchase->email;
        $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
        $attachments = $pdfFilePaths;
        $message = $this->messageFactory->create($from, $to, $replyTo, $mailContents->subject, $mailContents->body, $attachments);

        try {
            $this->mailer->send($message);
            $this->log->info('Sent customer purchase confirmation mail to ' . $to);
        } catch (\Nette\Mail\SendException $e) {
            $this->log->error($e);
        }
    }

    public function sendCustomerPurchaseNotification($purchase, $totalPrice) {
        $templatePath = $this->templateProvider->getPath('customer-purchase-notification', 'default', 'txt');

        $params = [
            'purchase' => $purchase,
            'reservations' => $purchase->reservations,
            'total' => $totalPrice,
            'hostName' => $this->hostName,
            'administrator' => $this->administrator
        ];
        
        $mailContents = $this->mailTemplateParser->parse($templatePath, $params);

        foreach ($this->settings['notification']['listeners'] as $listener) {
            $from = $this->settings['from'];
            $to = $listener;
            $replyTo = $this->settings['replyTo']['name'] . ' <' . $this->settings['replyTo']['email'] . '>';
            $attachments = [];
            $message = $this->messageFactory->create($from, $to, $replyTo, $mailContents->subject, $mailContents->body, $attachments);

            try {
                $this->mailer->send($message);
                $this->log->info('Sent customer purchase notification mail to ' . $to);
            } catch (\Nette\Mail\SendException $e) {
                $this->log->error($e);
            }
        }
    }
}