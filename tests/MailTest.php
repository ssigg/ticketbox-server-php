<?php

use Nette\Mail\IMailer;
use Latte\Engine;

class MailTest extends \PHPUnit_Framework_TestCase {
    private $mailerMock;
    private $templateMock;
    private $twigMock;
    private $templateProviderMock;
    private $messageFactoryMock;
    private $pdfTicketWriterMock;

    protected function setUp() {        
        $this->mailerMock = $this->getMockBuilder(IMailer::class)
            ->setMethods(['send'])
            ->getMock();

        $this->templateMock = $this->getMockBuilder(\Twig_TemplateInterface::class)
            ->setMethods(['render'])
            ->getMockForAbstractClass();

        $this->twigMock = $this->getMockBuilder(\Twig_Environment::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadTemplate'])
            ->getMockForAbstractClass();
        $this->twigMock->method('loadTemplate')->willReturn($this->templateMock);

        $this->templateProviderMock = $this->getMockBuilder(Services\TemplateProviderInterface::class)
            ->setMethods(['getPath'])
            ->getMockForAbstractClass();
        
        $this->messageFactoryMock = $this->getMockBuilder(Services\MessageFactoryInterface::class)
            ->setMethods(['create'])
            ->getMock();
        $this->messageFactoryMock
            ->method('create')
            ->willReturn(new \Nette\Mail\Message);

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
        $mail = new Services\Mail($this->twigMock, $this->templateProviderMock, $this->messageFactoryMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

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
        $mail = new Services\Mail($this->twigMock, $this->templateProviderMock, $this->messageFactoryMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

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
        $mail = new Services\Mail($this->twigMock, $this->templateProviderMock, $this->messageFactoryMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

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
        $mail = new Services\Mail($this->twigMock, $this->templateProviderMock, $this->messageFactoryMock, $this->mailerMock, $this->pdfTicketWriterMock, $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $reservationStub = new MailTestReservationStub('unique');
        $mail->sendBoxofficePurchaseConfirmation('Box office', 'john.doe@example.com', 'en', [ $reservationStub ], 0);
    }
}

class MailTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}