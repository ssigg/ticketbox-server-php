<?php

class SeatReserverTest extends \PHPUnit_Framework_TestCase {
    private $orderMapperMock;
    private $reservationMapperMock;
    private $reservationConverterMock;
    private $tokenProviderMock;

    protected function setUp() {
        $this->orderMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->reservationMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['where', 'first', 'update', 'delete', 'create'])
            ->getMockForAbstractClass();
        $this->reservationConverterMock = $this->getMockBuilder(Services\ReservationConverterInterface::class)
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
        $this->tokenProviderMock = $this->getMockBuilder(Services\TokenProviderInterface::class)
            ->setMethods(['provide'])
            ->getMockForAbstractClass();
    }
    
    public function testConstructorFetchesToken() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');

        $this->tokenProviderMock->expects($this->once())->method('provide');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
    }

    public function testReserveCreatesAReservationForEachSeat() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $seats = [
            $this->getEntityMock(),
            $this->getEntityMock(),
            $this->getEntityMock()
        ];
        $event = $this->getEntityMock();

        $this->reservationMapperMock->expects($this->exactly(count($seats)))->method('create');
        $reserver->reserve($seats, $event);
    }

    public function testReleaseDeletesReservationForEachSeat() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $seats = [
            $this->getEntityMock(),
            $this->getEntityMock(),
            $this->getEntityMock()
        ];
        $event = $this->getEntityMock();

        $this->reservationMapperMock->expects($this->exactly(count($seats)))->method('delete');
        $reserver->release($seats, $event);
    }

    public function testChangeReductionModifiesReservation() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $seat = $this->getEntityMock();
        $event = $this->getEntityMock();

        $this->reservationMapperMock
            ->method('first')
            ->willReturn($this->getEntityMock());

        $this->reservationMapperMock->expects($this->once())->method('update');
        $reserver->changeReduction($seat, $event, true);
    }

    public function testOrderWithoutReservationsDoesNothing() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        
        $reservations = [ ];
        $this->reservationMapperMock
            ->method('where')
            ->willReturn($reservations);
        $this->orderMapperMock
            ->method('create')
            ->willReturn($this->getEntityMock());
        
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->orderMapperMock->expects($this->never())->method('create');
        $reserver->order('John', 'Doe', 'john.doe@example.com');
    }

    public function testOrderCreatesOrder() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        
        $reservations = [
            $this->getEntityMock(),
            $this->getEntityMock(),
            $this->getEntityMock()
        ];
        $this->reservationMapperMock
            ->method('where')
            ->willReturn($reservations);
        $this->orderMapperMock
            ->method('create')
            ->willReturn($this->getEntityMock());
        
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->orderMapperMock->expects($this->once())->method('create');
        $reserver->order('John', 'Doe', 'john.doe@example.com');
    }

    public function testOrderModifiesAllReservations() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        
        $reservations = [
            $this->getEntityMock(),
            $this->getEntityMock(),
            $this->getEntityMock()
        ];
        $this->reservationMapperMock
            ->method('where')
            ->willReturn($reservations);
        $this->orderMapperMock
            ->method('create')
            ->willReturn($this->getEntityMock());
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);

        $this->reservationMapperMock->expects($this->exactly(count($reservations)))->method('update');
        $reserver->order('John', 'Doe', 'john.doe@example.com');
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        return $entityMock;
    }
}