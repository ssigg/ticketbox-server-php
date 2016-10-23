<?php

class SeatActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
    }

    public function testListSeatsAction() {
        $action = new Actions\ListSeatsAction($this->container);

        $request = $this->getGetRequest('/seats');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"block_id":1,"name":"Seat 1","x0":0,"y0":1,"x1":2,"y1":3,"x2":4,"y2":5,"x3":6,"y3":7}]',
            (string)$response->getBody());
    }

    public function testListSeatsOfNonEmptyBlockAction() {
        $action = new Actions\ListSeatsAction($this->container);

        $request = $this->getGetRequest('/seats?block_id=1');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ ]);
        $this->assertSame(
            '[{"id":1,"block_id":1,"name":"Seat 1","x0":0,"y0":1,"x1":2,"y1":3,"x2":4,"y2":5,"x3":6,"y3":7}]',
            (string)$response->getBody());
    }

    public function testListSeatsOfEmptyBlockAction() {
        $action = new Actions\ListSeatsAction($this->container);

        $request = $this->getGetRequest('/seats?block_id=42');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ ]);
        $this->assertSame(
            '[]',
            (string)$response->getBody());
    }

    public function testCreateSeatAction() {
        $action = new Actions\CreateSeatsAction($this->container);

        $seats = [
            [
                "name" => "New Seat 2",
                "block_id" => 1,
                "x0" => "x0",
                "y0" => "y0",
                "x1" => "x1",
                "y1" => "y1",
                "x2" => "x2",
                "y2" => "y2",
                "x3" => "x3",
                "y3" => "y3"
            ],
            [
                "name" => "New Seat 2",
                "block_id" => 1,
                "x0" => "x0",
                "y0" => "y0",
                "x1" => "x1",
                "y1" => "y1",
                "x2" => "x2",
                "y2" => "y2",
                "x3" => "x3",
                "y3" => "y3"
            ],
        ];

        $request = $this->getPostRequest('/seats', $seats);
        $response = new \Slim\Http\Response;

        $seatMapper = $this->container->orm->mapper('Model\Seat');

        $numberOfSeatsBefore = count($seatMapper->all());
        $action($request, $response, []);
        $numberOfSeatsAfter = count($seatMapper->all());
        
        $this->assertSame($numberOfSeatsBefore + 2, $numberOfSeatsAfter);
    }

    public function testDeleteSeatAction() {
        $action = new Actions\DeleteSeatAction($this->container);

        $request = $this->getDeleteRequest('/seats/1');
        $response = new \Slim\Http\Response();

        $seatMapper = $this->container->orm->mapper('Model\Seat');
        $reservationMapper = $this->container->orm->mapper('Model\Reservation');

        $numberOfSeatsBefore = count($seatMapper->all());
        $numberOfReservationsBefore = count($reservationMapper->all());
        $action($request, $response, [ 'id' => 1 ]);
        $numberOfSeatsAfter = count($seatMapper->all());
        $numberOfReservationsAfter = count($reservationMapper->all());
        
        $this->assertSame($numberOfSeatsBefore - 1, $numberOfSeatsAfter);
        $this->assertSame($numberOfReservationsBefore - 1, $numberOfReservationsAfter);
    }
}