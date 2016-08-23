<?php

class ReservationConverterTest extends \PHPUnit_Framework_TestCase {
    private $eventMapperMock;
    private $seatMapperMock;
    private $eventblockMapperMock;
    private $categoryMapperMock;
    private $converter;

    protected function setUp() {
        $this->eventMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->seatMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->eventblockMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['first'])
            ->getMockForAbstractClass();
        $this->categoryMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->converter = new Services\ReservationConverter(
            $this->eventMapperMock,
            $this->seatMapperMock,
            $this->eventblockMapperMock,
            $this->categoryMapperMock);
    }

    public function testNotReducedReservation() {
        $event = $this->getEntityMock();
        $this->eventMapperMock->method('get')->willReturn($event);

        $seat = $this->getEntityMock();
        $seat->method('get')->willReturn(1);
        $this->seatMapperMock->method('get')->willReturn($seat);

        $eventblock = $this->getEntityMock();
        $eventblock->method('get')->willReturn(1);
        $this->eventblockMapperMock->method('first')->willReturn($eventblock);

        $category = $this->getEntityMock();
        $categoryValueMap = [
            [ 'price', 20 ],
            [ 'price_reduced', 10 ]
        ];
        $category->method('get')->will($this->returnValueMap($categoryValueMap));
        $this->categoryMapperMock->method('get')->willReturn($category);

        $reservation = $this->getEntityMock();
        $reservationValueMap = [
            [ 'event_id', 1 ],
            [ 'seat_id', 1 ],
            [ 'is_reduced', false ]
        ];
        $reservation->method('get')->will($this->returnValueMap($reservationValueMap));

        $expandedReservations = $this->converter->convert([ $reservation ]);
        $this->assertSame(1, count($expandedReservations));
        $this->assertSame($event, $expandedReservations[0]->event);
        $this->assertSame($seat, $expandedReservations[0]->seat);
        $this->assertSame($category, $expandedReservations[0]->category);
        $this->assertSame(false, $expandedReservations[0]->isReduced);
        $this->assertSame(20, $expandedReservations[0]->price);
    }

    public function testReducedReservation() {
        $event = $this->getEntityMock();
        $this->eventMapperMock->method('get')->willReturn($event);

        $seat = $this->getEntityMock();
        $seat->method('get')->willReturn(1);
        $this->seatMapperMock->method('get')->willReturn($seat);

        $eventblock = $this->getEntityMock();
        $eventblock->method('get')->willReturn(1);
        $this->eventblockMapperMock->method('first')->willReturn($eventblock);

        $category = $this->getEntityMock();
        $categoryValueMap = [
            [ 'price', 20 ],
            [ 'price_reduced', 10 ]
        ];
        $category->method('get')->will($this->returnValueMap($categoryValueMap));
        $this->categoryMapperMock->method('get')->willReturn($category);

        $reservation = $this->getEntityMock();
        $reservationValueMap = [
            [ 'event_id', 1 ],
            [ 'seat_id', 1 ],
            [ 'is_reduced', true ]
        ];
        $reservation->method('get')->will($this->returnValueMap($reservationValueMap));

        $expandedReservations = $this->converter->convert([ $reservation ]);
        $this->assertSame(1, count($expandedReservations));
        $this->assertSame($event, $expandedReservations[0]->event);
        $this->assertSame($seat, $expandedReservations[0]->seat);
        $this->assertSame($category, $expandedReservations[0]->category);
        $this->assertSame(true, $expandedReservations[0]->isReduced);
        $this->assertSame(10, $expandedReservations[0]->price);
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        return $entityMock;
    }
}