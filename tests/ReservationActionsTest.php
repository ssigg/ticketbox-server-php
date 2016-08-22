<?php

class ReservationActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        $reserverMock = $this->getMockBuilder(SeatReserverInterface::class)
            ->setMethods(['reserve', 'release', 'addReduction', 'removeReduction'])
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
        $action($request, $response, [ 'seatId' => 42 ]); 
    }

    public function testUseReserverToAddReductionToReservation() {
        $action = new Actions\AddReductionToReservationAction($this->container);

        $request = $this->getPutRequest('/reservations', []);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('addReduction');
        $action($request, $response, [ 'seatId' => 42 ]); 
    }

    public function testUseReserverToRemoveReductionToReservation() {
        $action = new Actions\RemoveReductionFromReservationAction($this->container);

        $request = $this->getPutRequest('/reservations', []);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('removeReduction');
        $action($request, $response, [ 'seatId' => 42 ]); 
    }
}