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
    private $loggerMock;

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

        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->setMethods(['info', 'error'])
            ->getMockForAbstractClass();
    }

    public function testSendOrderConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'order-confirmation' => [
                'subject' => 'Confirmation Subject'
            ],
            'replyTo' => [
                'name' => 'Reply',
                'email' => 'reply@example.com'
            ]
        ];
        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $this->loggerMock
            ->expects($this->once())
            ->method('info');

        $mail->sendOrderConfirmation('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', [], 0);
    }

    public function testSendOrderConfirmationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'order-confirmation' => [
                'subject' => 'Confirmation Subject'
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
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $this->loggerMock
            ->expects($this->once())
            ->method('error');

        $mail->sendOrderConfirmation('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', [], 0);
    }

    public function testSendOrderNotification() {
        $settings = [
            'from' => 'from@example.com',
            'order-notification' => [
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

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('info');

        $mail->sendOrderNotification('John', 'Doe', 'john.doe@example.com', [], 0);
    }

    public function testSendOrderNotificationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'order-notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('error');

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
        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('info');

        $mail->sendBoxofficePurchaseNotification('Box office', [], 0);
    }

    public function testSendBoxofficePurchaseNotificationWithFailingMailer() {
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('error');

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
        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $this->loggerMock
            ->expects($this->once())
            ->method('info');

        $reservationStub = new MailTestReservationStub('unique');
        $mail->sendBoxofficePurchaseConfirmation('Box office', 'john.doe@example.com', 'en', [ $reservationStub ], 0);
    }

    public function testSendBoxofficePurchaseConfirmationWithFailingMailer() {
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $this->loggerMock
            ->expects($this->once())
            ->method('error');

        $reservationStub = new MailTestReservationStub('unique');
        $mail->sendBoxofficePurchaseConfirmation('Box office', 'john.doe@example.com', 'en', [ $reservationStub ], 0);
    }

    public function testSendCustomerPurchaseNotification() {
        $settings = [
            'from' => 'from@example.com',
            'purchase-confirmation' => [
                'subject' => 'Confirmation subject'
            ],
            'purchase-notification' => [
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

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('info');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseNotification($purchaseStub, 0);
    }

    public function testSendCustomerPurchaseNotificationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'purchase-confirmation' => [
                'subject' => 'Confirmation subject'
            ],
            'purchase-notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->exactly(2))->method('send');

        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('error');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseNotification($purchaseStub, 0);
    }

    public function testSendCustomerPurchaseConfirmation() {
        $settings = [
            'from' => 'from@example.com',
            'purchase-confirmation' => [
                'subject' => 'Confirmation subject'
            ],
            'purchase-notification' => [
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
        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $this->loggerMock
            ->expects($this->once())
            ->method('info');

        $reservationStub = new MailTestReservationStub('unique');
        $purchaseStub = new MailTestCustomerPurchaseStub('en', 'john.doe@example.com', [ $reservationStub ]);
        $mail->sendCustomerPurchaseConfirmation($purchaseStub, 0);
    }

    public function testSendCustomerPurchaseConfirmationWithFailingMailer() {
        $settings = [
            'from' => 'from@example.com',
            'purchase-confirmation' => [
                'subject' => 'Confirmation subject'
            ],
            'purchase-notification' => [
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

        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));

        $mail = new Services\Mail(
            $this->twigMock,
            $this->templateProviderMock,
            $this->messageFactoryMock,
            $this->mailerMock,
            $this->pdfTicketWriterMock,
            $this->loggerMock,
            $settings);

        $this->mailerMock->expects($this->once())->method('send');

        $this->loggerMock
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