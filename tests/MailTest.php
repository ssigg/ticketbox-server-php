<?php

use Nette\Mail\IMailer;
use Latte\Engine;

class MailTest extends \PHPUnit_Framework_TestCase {
    private $mailerMock;
    private $templateMock;
    private $pdfTicketWriterMock;

    protected function setUp() {        
        $this->mailerMock = $this->getMockBuilder(IMailer::class)
            ->setMethods(['send'])
            ->getMock();

        $this->templateMock = $this->getMockBuilder(Engine::class)
            ->setMethods(['renderToString'])
            ->getMock();

        $this->pdfTicketWriterMock = $this->getMockBuilder(Services\PdfTicketWriterInterface::class)
            ->setMethods(['write'])
            ->getMock();
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
        $mail = new Services\Mail($this->templateMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $mail->sendOrderConfirmation('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', [], 0);
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
        $mail = new Services\Mail($this->templateMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

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
            ]
        ];
        $mail = new Services\Mail($this->templateMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $mail->sendBoxofficePurchaseNotification('Box office', [], 0);
    }

    public function testSendBoxofficePurchaseConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'confirmation' => [
                'subject' => 'Confirmation subject'
            ],
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
        $mail = new Services\Mail($this->templateMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $mail->sendBoxofficePurchaseConfirmation('Box office', 'john.doe@example.com', 'en', [ 'reservation' ], 0);
    }
}