<?php

use Nette\Mail\IMailer;
use Latte\Engine;

class MailTest extends \PHPUnit_Framework_TestCase {
    private $templateProviderMock;
    private $mailTemplateParserMock;
    private $messageFactoryMock;
    private $mailerMock;
    private $pdfTicketWriterMock;
    private $loggerMock;

    protected function setUp() {
        $this->templateProviderMock = $this->getMockBuilder(Services\TemplateProviderInterface::class)
            ->setMethods(['getPath'])
            ->getMockForAbstractClass();

        $this->mailTemplateParserMock = $this->getMockBuilder(Services\MailTemplateParserInterface::class)
            ->setMethods(['parse'])
            ->getMockForAbstractClass();
        $this->mailTemplateParserMock->method('parse')->willReturn(new Services\MailContents('subject', 'body'));
        
        $this->messageFactoryMock = $this->getMockBuilder(Services\MessageFactoryInterface::class)
            ->setMethods(['create'])
            ->getMock();
        $this->messageFactoryMock
            ->method('create')
            ->willReturn(new \Nette\Mail\Message);

        $this->mailerMock = $this->getMockBuilder(IMailer::class)
            ->setMethods(['send'])
            ->getMock();

        $this->pdfTicketWriterMock = $this->getMockBuilder(Services\PdfTicketWriterInterface::class)
            ->setMethods(['write'])
            ->getMock();

        $this->logMock = $this->getMockBuilder(Services\LogInterface::class)
            ->setMethods(['info', 'error'])
            ->getMockForAbstractClass();
    }

    public function testSendOrderConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'replyTo' => [
                'name' => 'Reply',
                'email' => 'reply@example.com'
            ]
        ];

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->once())->method('send');

        $this->logMock
            ->expects($this->once())
            ->method('info');

        $mail->sendOrderConfirmation('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', [], 0);
    }

    public function testSendOrderConfirmationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'replyTo' => [
                'name' => 'Reply',
                'email' => 'reply@example.com'
            ]
        ];

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->once())->method('send');

        $this->logMock
            ->expects($this->once())
            ->method('error');

        $mail->sendOrderConfirmation('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', [], 0);
    }

    public function testSendOrderNotification() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->logMock
            ->expects($this->exactly(2))
            ->method('info');

        $mail->sendOrderNotification('John', 'Doe', 'john.doe@example.com', [], 0);
    }

    public function testSendOrderNotificationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->logMock
            ->expects($this->exactly(2))
            ->method('error');

        $mail->sendOrderNotification('John', 'Doe', 'john.doe@example.com', [], 0);
    }

    public function testSendBoxofficePurchaseNotification() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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
        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->logMock
            ->expects($this->exactly(2))
            ->method('info');

        $mail->sendBoxofficePurchaseNotification('Box office', [], 0);
    }

    public function testSendBoxofficePurchaseNotificationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->logMock
            ->expects($this->exactly(2))
            ->method('error');

        $mail->sendBoxofficePurchaseNotification('Box office', [], 0);
    }

    public function testSendBoxofficePurchaseConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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
        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->once())->method('send');

        $this->logMock
            ->expects($this->once())
            ->method('info');

        $reservationStub = new MailTestReservationStub('unique');
        $mail->sendBoxofficePurchaseConfirmation('Box office', 'john.doe@example.com', 'en', [ $reservationStub ], 0);
    }

    public function testSendBoxofficePurchaseConfirmationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->once())->method('send');

        $this->logMock
            ->expects($this->once())
            ->method('error');

        $reservationStub = new MailTestReservationStub('unique');
        $mail->sendBoxofficePurchaseConfirmation('Box office', 'john.doe@example.com', 'en', [ $reservationStub ], 0);
    }

    public function testSendCustomerPurchaseNotification() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->logMock
            ->expects($this->exactly(2))
            ->method('info');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseNotification($purchaseStub, 0);
    }

    public function testSendCustomerPurchaseNotificationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->logMock
            ->expects($this->exactly(2))
            ->method('error');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseNotification($purchaseStub, 0);
    }

    public function testSendCustomerPurchaseConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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
        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->once())->method('send');

        $this->logMock
            ->expects($this->once())
            ->method('info');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseConfirmation($purchaseStub, 0);
    }

    public function testSendCustomerPurchaseConfirmationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->templateProviderMock,
            $this->mailTemplateParserMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->logMock,
            $settings,
            'Host Name',
            [ 'firstname' => 'Admi', 'lastname' => 'Nistrator', 'email' => 'admin@example.com' ]);

        $this->mailerMock->expects($this->once())->method('send');

        $this->logMock
            ->expects($this->once())
            ->method('error');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseConfirmation($purchaseStub, 0);
    }
}

class MailTestCustomerPurchaseStub {
    public $locale;
    public $email;
    public $reservations;

    public function __construct($locale, $email, $reservations) {
        $this->locale = $locale;
        $this->email = $email;
        $this->reservations = $reservations;
    }
}

class MailTestReservationStub implements Services\ExpandedReservationInterface {
    public $unique_id;
    
    public function __construct($unique_id) {
        $this->unique_id = $unique_id;
    }
}