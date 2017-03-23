<?php

class LogTest extends \PHPUnit_Framework_TestCase {
    private $messageFactoryMock;
    private $mailerMock;
    private $loggerMock;
    private $settings;

    protected function setUp() {
        $this->messageFactoryMock = $this->getMockBuilder(Services\MessageFactoryInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->messageFactoryMock
            ->method('create')
            ->willReturn(new \Nette\Mail\Message);

        $this->mailerMock = $this->getMockBuilder(\Nette\Mail\IMailer::class)
            ->setMethods(['send'])
            ->getMockForAbstractClass();
        
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->setMethods(['info', 'warning', 'error'])
            ->getMockForAbstractClass();

        $this->settings = [
            'from' => 'from@example.com',
            'subject' => 'subject',
            'listeners' => [ 'listener1@example.com' ]
        ];
        
        $this->log = new Services\Log($this->messageFactoryMock, $this->mailerMock, $this->loggerMock, $this->settings);
    }
    
    public function testInfoLogsInfoAndDoesNotSendMail() {
        $this->loggerMock->expects($this->once())->method('info');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->never())->method('error');
        $this->messageFactoryMock->expects($this->never())->method('create');
        $this->mailerMock->expects($this->never())->method('send');
        $this->log->info('message');
    }

    public function testWarningLogsWarningAndDoesNotSendMail() {
        $this->loggerMock->expects($this->never())->method('info');
        $this->loggerMock->expects($this->once())->method('warning');
        $this->loggerMock->expects($this->never())->method('error');
        $this->messageFactoryMock->expects($this->never())->method('create');
        $this->mailerMock->expects($this->never())->method('send');
        $this->log->warning('message');
    }

    public function testErrorLogsErrorAndSendsMailAndLogsThat() {
        $this->loggerMock->expects($this->once())->method('info');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->once())->method('error');
        $this->messageFactoryMock->expects($this->once())->method('create');
        $this->mailerMock->expects($this->once())->method('send');
        $this->log->error('message');
    }

    public function testErrorLogsErrorAndLogsWhenMailerFails() {
        $this->loggerMock->expects($this->never())->method('info');
        $this->loggerMock->expects($this->never())->method('warning');
        $this->loggerMock->expects($this->exactly(2))->method('error');
        $this->messageFactoryMock->expects($this->once())->method('create');
        $this->mailerMock
            ->method('send')
            ->will($this->throwException(new \Nette\Mail\SendException));
        $this->log->error('message');
    }
}