<?php

class ReservationActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        $reserverMock = $this->getMockBuilder(SeatReserverInterface::class)
            ->setMethods(['getReservations', 'reserve', 'release', 'changeReduction'])
            ->getMock();
        $this->container['seatReserver'] = $reserverMock;

        $reservationConverterMock = $this->getMockBuilder(ReservationConverterInterface::class)
            ->setMethods(['convert'])
            ->getMock();
        $this->container['reservationConverter'] = $reservationConverterMock;
    }

    public function testListMyReservationsAction() {
        $action = new Actions\ListMyReservationsAction($this->container);

        $request = $this->getGetRequest('/reservations');
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('getReservations');
        $action($request, $response, []);
    }

    public function testUseReserverToCreateReservationUnsuccessful() {
        $action = new Actions\CreateReservationAction($this->container);

        $data = [
            "seat_id" => 2,
            "event_id" => 42
        ];
        $request = $this->getPostRequest('/reservations', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');
        $reserverMock->method('reserve')->willReturn(null);
        $reserverMock->expects($this->once())->method('reserve');

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock->expects($this->never())->method('convert');
        
        $action($request, $response, []); 
    }

    public function testUseReserverToCreateReservationSuccessful() {
        $action = new Actions\CreateReservationAction($this->container);

        $data = [
            "seat_id" => 2,
            "event_id" => 42
        ];
        $request = $this->getPostRequest('/reservations', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');
        $reserverMock->method('reserve')->willReturn($this->getEntityMock());
        $reserverMock->expects($this->once())->method('reserve');

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock->expects($this->once())->method('convert');
        
        $action($request, $response, []); 
    }

    public function testUseReserverToDeleteReservation() {
        $action = new Actions\DeleteReservationAction($this->container);

        $request = $this->getDeleteRequest('/reservations/42');
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('release');
        $action($request, $response, [ 'id' => 42 ]); 
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
        $action($request, $response, [ 'id' => 42 ]); 
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->getMockForAbstractClass();
        return $entityMock;
    }
}