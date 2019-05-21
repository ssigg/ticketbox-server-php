<?php

namespace Services;

interface LogInterface {
    function info($message);
    function warning($message);
    function error($message);
}

class Log implements LogInterface {
    private $messageFactory;
    private $mailer;
    private $logger;
    private $settings;

    public function __construct(
        MessageFactoryInterface $messageFactory,
        \Nette\Mail\IMailer $mailer,
        \Psr\Log\LoggerInterface $logger,
        $settings) {
        $this->messageFactory = $messageFactory;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->settings = $settings;
    }

    public function info($message) {
        $this->logger->info($message);
    }

    public function warning($message) {
        $this->logger->warning($message);
    }
    public function error($message) {
        $this->logger->error($message);
        $this->sendMailToListeners($message);
    }

    private function sendMailToListeners($messageText) {
        $from = $this->settings['from'];
        $replyTo = $this->settings['from'];
        $subject = $this->settings['subject'];
        $attachments = [];

        foreach ($this->settings['listeners'] as $listener) {
            $message = $this->messageFactory->create($from, $listener, $replyTo, $subject, $messageText, $attachments);
            try {
                $this->mailer->send($message);
                $this->logger->info('Sent log message to ' . $listener);
            } catch (\Nette\Mail\SendException $e) {
                $this->logger->error($e);
            }
        }
    }
}