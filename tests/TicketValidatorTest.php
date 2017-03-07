<?php

class TicketValidatorTest extends \PHPUnit_Framework_TestCase {
    private $reservationMapperMock;
    private $eventMapperMock;
    private $loggerMock;
    private $secretKey;
    private $validator;

    protected function setUp() {
        $this->reservationMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['first', 'update'])
            ->getMockForAbstractClass();
        $this->eventMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->setMethods(['info', 'warning'])
            ->getMockForAbstractClass();
        $this->secretKey = 'validSecretKey';
        $this->validator = new Services\TicketValidator($this->reservationMapperMock, $this->loggerMock, $this->secretKey);
        $this->testValidator = new Services\TicketTestValidator($this->eventMapperMock, $this->secretKey);
    }

    public function testWhenEverythingIsOkThenTheStatusIsOk() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'code';
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($this->getReservationMock('boxoffice-purchase', false));
        $this->loggerMock
            ->expects($this->once())
            ->method('info');
        $result = $this->validator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Ok, $result->status);
    }

    public function testWhenEverythingIsOkThenTheReservationIsUpdated() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'code';
        $reservationStub = $this->getReservationMock('boxoffice-purchase', false);
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($reservationStub);
        $this->reservationMapperMock
            ->expects($this->once())
            ->method('update')
            ->with($this->equalTo($reservationStub));
        $this->validator->validate($key, $eventId, $code);
    }

    public function testWhenKeyIsInvalidThenTheStatusIsError() {
        $key = 'invalidSecretKey';
        $eventId = 'e1';
        $code = 'code';
        $this->loggerMock
            ->expects($this->once())
            ->method('warning');
        $result = $this->validator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
    }

    public function testWhenReservationIsNotFoundThenTheStatusIsError() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'code';
        $this->reservationMapperMock
            ->method('first')
            ->willReturn(null);
        $this->loggerMock
            ->expects($this->once())
            ->method('warning');
        $result = $this->validator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
    }

    public function testWhenReservationOrderKindIsNotPurchaseThenTheStatusIsError() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'code';
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($this->getReservationMock('not-purchase', false));
        $this->loggerMock
            ->expects($this->once())
            ->method('warning');
        $result = $this->validator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
    }

    public function testWhenReservationIsAlreadyScannedThenTheStatusIsWarning() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'code';
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($this->getReservationMock('boxoffice-purchase', true));
        $this->loggerMock
            ->expects($this->once())
            ->method('warning');
        $result = $this->validator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Warning, $result->status);
    }

    public function testTestValidatorWhenKeyIsInvalidThenTheStatusIsErrorAndNoEventIsNotFetched() {
        $key = 'invalidSecretKey';
        $eventId = 'e1';
        $code = 'code';

        $this->eventMapperMock
            ->expects($this->never())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
    }

    public function testTestValidatorWhenEventIsNotFoundThenTheStatusIsError() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'code';

        $this->eventMapperMock
            ->method('get')
            ->willReturn(null);
        
        $this->eventMapperMock
            ->expects($this->once())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
    }

    public function testTestValidatorWhenCodeIsOkThenTheStatusIsOk() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'ok';

        $this->eventMapperMock
            ->method('get')
            ->willReturn($this->getEventMock('name', 'dateandtime', 'location'));
        
        $this->eventMapperMock
            ->expects($this->once())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Ok, $result->status);
    }

    public function testTestValidatorWhenCodeIsErrorThenTheStatusIsError() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'error';

        $this->eventMapperMock
            ->method('get')
            ->willReturn($this->getEventMock('name', 'dateandtime', 'location'));
        
        $this->eventMapperMock
            ->expects($this->once())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
    }

    public function testTestValidatorWhenCodeIsWarningThenTheStatusIsWarning() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'warning';

        $this->eventMapperMock
            ->method('get')
            ->willReturn($this->getEventMock('name', 'dateandtime', 'location'));
        
        $this->eventMapperMock
            ->expects($this->once())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Warning, $result->status);
    }

    public function testTestValidatorWhenEventIsFoundButCodeIsUnknownThenExceptionIsThrown() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'unknown';

        $this->eventMapperMock
            ->method('get')
            ->willReturn($this->getEventMock('name', 'dateandtime', 'location'));
        
        $this->eventMapperMock
            ->expects($this->once())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertSame(Services\TicketValidatorStatus::Error, $result->status);
        $this->assertSame('Unknown code ' . $code, $result->messages[0]);
    }

    public function testTestValidatorWhenEventIsFoundThenSomeEventInformationsAreInMessages() {
        $key = 'validSecretKey';
        $eventId = 'e1';
        $code = 'ok';

        $name = 'event-name';
        $dateandtime = 'event-dateandtime';
        $location = 'event-location';
        $this->eventMapperMock
            ->method('get')
            ->willReturn($this->getEventMock($name, $dateandtime, $location));
        
        $this->eventMapperMock
            ->expects($this->once())
            ->method('get');

        $result = $this->testValidator->validate($key, $eventId, $code);
        $this->assertContains($code, $result->messages[0]);
        $this->assertContains($name, $result->messages[1]);
        $this->assertContains($dateandtime, $result->messages[2]);
        $this->assertContains($location, $result->messages[3]);
    }

    private function getReservationMock($order_kind, $is_scanned) {
        $valueMap = [
            [ 'order_kind', $order_kind ],
            [ 'is_scanned', $is_scanned ]
        ];
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $entityMock
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        return $entityMock;
    }

    private function getEventMock($name, $dateandtime, $location) {
        $valueMap = [
            [ 'name', $name ],
            [ 'dateandtime', $dateandtime ],
            [ 'location', $location ]
        ];
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $entityMock
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        return $entityMock;
    }
}