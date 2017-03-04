<?php

class OrderToBoxofficePurchaseUpgraderTest extends \PHPUnit_Framework_TestCase {
    private $orderMapperMock;
    private $boxofficePurchaseMapperMock;
    private $reservationMapperMock;
    private $reservationConverterMock;
    private $upgrader;

    protected function setUp() {
        $this->orderMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['delete'])
            ->getMockForAbstractClass();
        $this->boxofficePurchaseMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->reservationMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['update'])
            ->getMockForAbstractClass();
        $this->reservationConverterMock = $this->getMockBuilder(Services\ReservationConverterInterface::class)
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
        $defaultPriceModificators = [
            'factor' => 1,
            'addend' => 0
        ];
        $this->upgrader = new Services\OrderToBoxofficePurchaseUpgrader(
            $this->orderMapperMock,
            $this->boxofficePurchaseMapperMock,
            $this->reservationMapperMock,
            $this->reservationConverterMock,
            $defaultPriceModificators);
    }

    public function testReservationsAreFetchedOnceForTheOrderAndOnceForTheCreatedBoxofficePurchase() {
        $orderId = 42;
        $boxofficePurchaseId = 84;

        $orderMock = $this->getEntityMock();
        $orderMock
            ->method('get')
            ->willReturn($orderId);
        
        $this->reservationMapperMock
            ->method('where')
            ->willReturn([]);

        $this->reservationConverterMock
            ->method('convert')
            ->will($this->returnArgument(0));

        $boxofficePurchaseMock = $this->getEntityMock();
        $boxofficePurchaseMock
            ->method('get')
            ->willReturn($boxofficePurchaseId);
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($boxofficePurchaseMock);
        
        $this->reservationMapperMock
            ->expects($this->exactly(2))
            ->method('where')
            ->with($this->logicalOr(
                [ 'order_id' => $orderId, 'order_kind' => 'reservation' ],
                [ 'order_id' => $boxofficePurchaseId, 'order_kind' => 'boxoffice-purchase' ]
            ));
        $this->upgrader->upgrade($orderMock, 'boxoffice', 'en');
    }

    public function testReservationsAreConvertedTwice() {
        $orderId = 42;

        $orderMock = $this->getEntityMock();
        $orderMock
            ->method('get')
            ->willReturn($orderId);
        
        $reservations = [ $this->getEntityMock() ];
        $this->reservationMapperMock
            ->method('where')
            ->willReturn($reservations);

        $expandedReservationStub = new OrderToBoxofficePurchaseUpgraderTestReservationStub(42);
        $this->reservationConverterMock
            ->method('convert')
            ->willReturn([ $expandedReservationStub ]);
        
        $boxofficePurchaseMock = $this->getEntityMock();
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($boxofficePurchaseMock);
        
        $this->reservationConverterMock
            ->expects($this->exactly(2))
            ->method('convert')
            ->with($reservations);
        $this->upgrader->upgrade($orderMock, 'boxoffice', 'en');
    }

    public function testBoxofficePurchaseIsCreated() {
        $orderId = 42;

        $orderMock = $this->getEntityMock();
        $orderMock
            ->method('get')
            ->willReturn($orderId);
        
        $this->reservationMapperMock
            ->method('where')
            ->willReturn([]);

        $this->reservationConverterMock
            ->method('convert')
            ->will($this->returnArgument(0));

        $boxofficePurchaseMock = $this->getEntityMock();
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($boxofficePurchaseMock);

        $this->boxofficePurchaseMapperMock
            ->expects($this->once())
            ->method('create');
        $this->upgrader->upgrade($orderMock, 'boxoffice', 'en');
    }

    public function testOrderIsDeleted() {
        $orderId = 42;

        $orderMock = $this->getEntityMock();
        $orderMock
            ->method('get')
            ->willReturn($orderId);
        
        $this->reservationMapperMock
            ->method('where')
            ->willReturn([]);

        $this->reservationConverterMock
            ->method('convert')
            ->will($this->returnArgument(0));

        $boxofficePurchaseMock = $this->getEntityMock();
        $this->boxofficePurchaseMapperMock
            ->method('create')
            ->willReturn($boxofficePurchaseMock);

        $this->orderMapperMock
            ->expects($this->once())
            ->method('delete');
        $this->upgrader->upgrade($orderMock, 'boxoffice', 'en');
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        return $entityMock;
    }
}

class OrderToBoxofficePurchaseUpgraderTestReservationStub implements Services\ExpandedReservationInterface {
    public $price;
    
    public function __construct($price) {
        $this->price = $price;
    }
}