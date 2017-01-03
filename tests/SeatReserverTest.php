<?php

class SeatReserverTest extends \PHPUnit_Framework_TestCase {
    private $orderMapperMock;
    private $boxofficePurchaseMapperMock;
    private $customerPurchaseMapperMock;
    private $reservationMapperMock;
    private $reservationConverterMock;
    private $tokenProviderMock;

    protected function setUp() {
        $this->orderMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->boxofficePurchaseMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->customerPurchaseMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->reservationMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['where', 'first', 'update', 'delete', 'insert', 'get'])
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
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
    }

    public function testGetReservationsReserveGetsExpandedReservations() {
        $seatMocks = [
            $this->getEntityMock(),
            $this->getEntityMock()
        ];
        $this->reservationMapperMock
            ->method('where')
            ->willReturn($seatMocks);
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $this->reservationConverterMock
            ->method('convert')
            ->will($this->returnArgument(0));
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->reservationMapperMock->expects($this->once())->method('where');
        $this->reservationConverterMock->expects($this->once())->method('convert');
        $reservations = $reserver->getReservations();
        $this->assertSame(count($seatMocks), count($reservations));
    }

    public function testReserveCreatesReservationSuccessful() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $seat = $this->getEntityMock();
        $event = $this->getEntityMock();
        $category = $this->getEntityMock();

        $this->reservationMapperMock->method('insert')->willReturn(42);
        $this->reservationMapperMock->expects($this->once())->method('insert');
        $this->reservationMapperMock->expects($this->once())->method('get');
        $reserver->reserve($seat, $event, $category);
    }

    public function testReserveCreatesReservationUnsuccessful() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $seat = $this->getEntityMock();
        $event = $this->getEntityMock();
        $category = $this->getEntityMock();

        $this->reservationMapperMock->method('insert')->willReturn(false);
        $this->reservationMapperMock->expects($this->once())->method('insert');
        $this->reservationMapperMock->expects($this->never())->method('get');
        $reserver->reserve($seat, $event, $category);
    }

    public function testReleaseDeletesReservation() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $reservationId = 1;
        $event = $this->getEntityMock();

        $this->reservationMapperMock->expects($this->once())->method('delete');
        $reserver->release($reservationId, $event);
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
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
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
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->orderMapperMock->expects($this->never())->method('create');
        $reserver->order('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', false);
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
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->orderMapperMock->expects($this->once())->method('create');
        $reserver->order('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', false);
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
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);

        $this->reservationMapperMock->expects($this->exactly(count($reservations)))->method('update');
        $reserver->order('Mr.', 'John', 'Doe', 'john.doe@example.com', 'en', false);
    }

    public function testBoxofficePurchaseWithoutReservationsDoesNothing() {
        $this->tokenProviderMock
            ->method('provide')
            ->willReturn('token');
        
        $reservations = [ ];
        $this->reservationMapperMock
            ->method('where')
            ->willReturn($reservations);
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($this->getEntityMock());
        
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->boxofficePurchaseMapperMock->expects($this->never())->method('create');
        $reserver->boxofficePurchase('Box office', 'en');
    }

    public function testBoxofficePurchaseCreatesBoxofficePurchase() {
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
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($this->getEntityMock());
        
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);
        
        $this->boxofficePurchaseMapperMock->expects($this->once())->method('create');
        $reserver->boxofficePurchase('Box office', 'en');
    }

    public function testBoxofficePurchaseModifiesAllReservations() {
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
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($this->getEntityMock());
        $settings = [
            'lifetimeInSeconds' => 0
        ];
        $reserver = new Services\SeatReserver(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->customerPurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $this->tokenProviderMock,
            $settings);

        $this->reservationMapperMock->expects($this->exactly(count($reservations)))->method('update');
        $reserver->boxofficePurchase('Box office', 'en');
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        return $entityMock;
    }
}