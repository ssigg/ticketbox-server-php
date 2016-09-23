<?php

use Nette\Mail\IMailer;
use Latte\Engine;

class MailTest extends \PHPUnit_Framework_TestCase {
    private $container;

    protected function setUp() {
        $this->container = new \Slim\Container;
        $mailerMock = $this->getMockBuilder(IMailer::class)
            ->setMethods(['send'])
            ->getMock();
        $this->container['mailer'] = $mailerMock;
        $templateMock = $this->getMockBuilder(Engine::class)
            ->setMethods(['renderToString'])
            ->getMock();
        $this->container['template'] = $templateMock;
    }

    public function testSendOrderConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'confirmation' => [
                'subject' => 'Confirmation Subject'
            ],
            'replyTo' => [
                'name' => 'Reply',
                'email' => 'reply@example.com'
            ]
        ];
        $mail = new Services\Mail($this->container->get('template'), $this->container->get('mailer'), $settings);

        $mailerMock = $this->container->get('mailer');
        $mailerMock->expects($this->once())->method('send');

        $mail->sendOrderConfirmation('Mr.', 'John', 'Doe', 'john.doe@example.com', [], 0);
    }

    public function testSendOrderNotification() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
                'subject' => 'Notification Subject',
                'listeners' => [
                    'listener.1@example.com',
                    'listener.2@example.com'
                ]
            ],
            'replyTo' => [
                'name' => 'Reply',
                'email' => 'reply@example.com'
            ]
        ];
        $mail = new Services\Mail($this->container->get('template'), $this->container->get('mailer'), $settings);

        $mailerMock = $this->container->get('mailer');
        $mailerMock->expects($this->exactly(2))->method('send');

        $mail->sendOrderNotification('John', 'Doe', 'john.doe@example.com', [], 0);
    }

    public function testSendBoxofficePurchaseNotification() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
                'subject' => 'Notification Subject',
                'listeners' => [
                    'listener.1@example.com',
                    'listener.2@example.com'
                ]
            ],
            'replyTo' => [
                'name' => 'Reply',
                'email' => 'reply@example.com'
            ],
            'boxoffice' => [
                'name' => 'Box office'
            ]
        ];
        $mail = new Services\Mail($this->container->get('template'), $this->container->get('mailer'), $settings);

        $mailerMock = $this->container->get('mailer');
        $mailerMock->expects($this->exactly(2))->method('send');

        $mail->sendBoxofficePurchaseNotification('Box office', [], 0);
    }
}