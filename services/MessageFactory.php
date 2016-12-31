<?php

namespace Services;

interface MessageFactoryInterface {
    function create($from, $to, $replyTo, $subject, $body, $attachments);
}

class MessageFactory implements MessageFactoryInterface {
    public function create($from, $to, $replyTo, $subject, $body, $attachments) {
        $message = new \Nette\Mail\Message;
        $message
            ->setFrom($from)
            ->setSubject($subject)
            ->addReplyTo($replyTo)
            ->addTo($to)
            ->setBody($body);
        foreach($attachments as $attachment) {
            $message->addAttachment($attachment);
        }
        return $message;
    }
}