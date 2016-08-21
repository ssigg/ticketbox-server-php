<?php

class SeatConverterTest extends \PHPUnit_Framework_TestCase {
    private $reservationMapperMock;
    private $tokenProviderMock;
    private $converter;

    protected function setUp() {
        $this->reservationMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['first', 'delete'])
            ->getMockForAbstractClass();
        $this->tokenProviderMock = $this->getMockBuilder(Services\TokenProviderInterface::class)
            ->setMethods(['provide'])
            ->getMockForAbstractClass();
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $this->converter = new Services\SeatConverter($this->reservationMapperMock, $this->tokenProviderMock, $settings);
    }

    public function testConvertDeletesStaleReservations() {
        $seats = [ ];
        $eventblock = null;

        $this->reservationMapperMock->expects($this->once())->method('delete');
        $this->converter->convert($seats, $eventblock);
    }

    public function testConvertFreeSeat() {
        $seats = [
            $this->getEntityMock()
        ];
        $eventblock = $this->getEntityMock();

        $this->reservationMapperMock
            ->method('first')
            ->willReturn(null);

        $convertedSeats = $this->converter->convert($seats, $eventblock);
        $this->assertSame('free', $convertedSeats[0]->state);
    }

    public function testConvertReservedSeat() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token1');

        $seats = [
            $this->getEntityMock()
        ];
        $eventblock = $this->getEntityMock();

        $reservationMock = $this->getEntityMock();
        $map = [
            ['token', 'token2'],
            ['order_id', null]
        ];
        $reservationMock
            ->method('get')
            ->will($this->returnValueMap($map));
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($reservationMock);

        $convertedSeats = $this->converter->convert($seats, $eventblock);
        $this->assertSame('reserved', $convertedSeats[0]->state);
    }

    public function testConvertReservedByMyselfSeat() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token1');

        $seats = [
            $this->getEntityMock()
        ];
        $eventblock = $this->getEntityMock();

        $reservationMock = $this->getEntityMock();
        $map = [
            ['token', 'token1'],
            ['order_id', null]
        ];
        $reservationMock
            ->method('get')
            ->will($this->returnValueMap($map));
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($reservationMock);

        $convertedSeats = $this->converter->convert($seats, $eventblock);
        $this->assertSame('reservedbymyself', $convertedSeats[0]->state);
    }

    public function testConvertOrderedSeat() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token1');

        $seats = [
            $this->getEntityMock()
        ];
        $eventblock = $this->getEntityMock();

        $reservationMock = $this->getEntityMock();
        $map = [
            ['token', 'token2'],
            ['order_id', 1]
        ];
        $reservationMock
            ->method('get')
            ->will($this->returnValueMap($map));
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($reservationMock);

        $convertedSeats = $this->converter->convert($seats, $eventblock);
        $this->assertSame('ordered', $convertedSeats[0]->state);
    }

    public function testConvertOrderedByMyselfSeat() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token1');

        $seats = [
            $this->getEntityMock()
        ];
        $eventblock = $this->getEntityMock();

        $reservationMock = $this->getEntityMock();
        $map = [
            ['token', 'token1'],
            ['order_id', 1]
        ];
        $reservationMock
            ->method('get')
            ->will($this->returnValueMap($map));
        $this->reservationMapperMock
            ->method('first')
            ->willReturn($reservationMock);

        $convertedSeats = $this->converter->convert($seats, $eventblock);
        $this->assertSame('ordered', $convertedSeats[0]->state);
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        return $entityMock;
    }
}