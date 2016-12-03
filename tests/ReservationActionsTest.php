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

    public function testListAllReservationsAction() {
        $reservationMapper = $this->container['orm']->mapper('Model\Reservation');

        $reservationMapper->create([
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'reservation',
            'is_reduced' => false,
            'timestamp' => time()]);

        $action = new Actions\ListAllReservationsAction($this->container);

        $request = $this->getGetRequest('/reservations');
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');
        $reserverMock->expects($this->never())->method('getReservations');

        $response = $action($request, $response, []);

        $responseAsArray = json_decode((string)$response->getBody(), true);
        $this->assertSame(1, count($responseAsArray));
        
        $reservation = $responseAsArray[0];
        $this->assertSame(2, $reservation['id']);
        $this->assertSame('abc', $reservation['token']);
        $this->assertSame(2, $reservation['seat_id']);
        $this->assertSame(1, $reservation['event_id']);
        $this->assertSame(false, $reservation['is_reduced']);
        $this->assertSame(1, $reservation['order_id']);
        $this->assertSame('reservation', $reservation['order_kind']);

    }

    public function testUseReserverToCreateReservationUnsuccessful() {
        $action = new Actions\CreateReservationAction($this->container);

        $data = [
            "seat_id" => 2,
            "event_id" => 42,
            "category_id" => 32
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
            "event_id" => 42,
            "category_id" => 32
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