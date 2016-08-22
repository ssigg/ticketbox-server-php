<?php

class ReservationActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        $reserverMock = $this->getMockBuilder(SeatReserverInterface::class)
            ->setMethods(['reserve', 'release', 'changeReduction'])
            ->getMock();
        $this->container['seatReserver'] = $reserverMock;
    }

    public function testUseReserverToCreateReservation() {
        $action = new Actions\CreateReservationAction($this->container);

        $data = [
            "seatId" => 2,
            "eventId" => 42
        ];
        $request = $this->getPostRequest('/reservations', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('reserve');
        $action($request, $response, []); 
    }

    public function testUseReserverToDeleteReservation() {
        $action = new Actions\DeleteReservationAction($this->container);

        $request = $this->getDeleteRequest('/reservations');
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('release');
        $action($request, $response, [ 'seatId' => 42, 'eventId' => 2 ]); 
    }

    public function testUseReserverToChangeReductionToReservation() {
        $action = new Actions\ChangeReductionForReservationAction($this->container);

        $data = [
            "isReduced" => true
        ];
        $request = $this->getPutRequest('/reservations', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('changeReduction');
        $action($request, $response, [ 'seatId' => 42, 'eventId' => 2 ]); 
    }
}