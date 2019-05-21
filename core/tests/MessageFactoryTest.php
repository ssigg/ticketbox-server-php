<?php

class MessageFactoryTest extends \PHPUnit_Framework_TestCase {
    private $existingFilePath;
    private $factory;

    protected function setUp() {
        $this->existingFilePath = 'foo.txt';
        file_put_contents($this->existingFilePath, 'foo');

        $this->factory = new Services\MessageFactory();
    }

    protected function tearDown() {
        unlink($this->existingFilePath);
    }

    public function testCreatesAMessage() {
        $from = 'from@example.com';
        $to = 'to@example.com';
        $replyTo = 'replyto@example.com';
        $subject = 'subject';
        $body = 'body';
        $attachments = [
            $this->existingFilePath
        ];
        $message = $this->factory->create($from, $to, $replyTo, $subject, $body, $attachments);

        $this->assertSame([ $from => null ], $message->getHeader('From'));
        $this->assertSame([ $to => null ], $message->getHeader('To'));
        $this->assertSame([ $replyTo => null ], $message->getHeader('Reply-To'));
        $this->assertSame($subject, $message->getHeader('Subject'));
        $this->assertSame($body, $message->getBody());
        $this->assertContains($this->existingFilePath, $message->getAttachments()[0]->getHeaders()['Content-Disposition']);
    }
}