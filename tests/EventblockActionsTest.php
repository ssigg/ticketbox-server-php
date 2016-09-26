<?php

class EventblockActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        $seatConverterMock = $this->getMockBuilder(SeatConverterInterface::class)
            ->setMethods(['convert'])
            ->getMock();
        $seatConverterMock
            ->method('convert')
            ->will($this->returnArgument(0));
        $this->container['seatConverter'] = $seatConverterMock;
    }

    public function testListEventblocksAction() {
        $action = new Actions\ListEventblocksAction($this->container);

        $request = $this->getGetRequest('/eventblocks');
        $response = new \Slim\Http\Response;

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"event_id":1,"block_id":1,"category_id":1}]',
            (string)$response->getBody());
    }

    public function testGetNotFoundResponse() {
        $action = new Actions\GetEventblockAction($this->container);

        $request = $this->getGetRequest('/eventblocks/notExisting');
        $response = new \Slim\Http\Response;

        $response = $action($request, $response, [ 'id' => 'notExisting' ]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetEventblockActionResponse() {
        $action = new Actions\GetEventblockAction($this->container);

        $request = $this->getGetRequest('/eventblocks/1');
        $response = new \Slim\Http\Response;

        $response = $action($request, $response, [ 'id' => 1 ]);
        $this->assertSame(
            '{"id":1,"event_id":1,"block_id":1,"category_id":1,"block":{"id":1,"seatplan_image_data_url":"data_url","name":"Block 1"},"category":{"id":1,"name":"Category 1","price":2,"price_reduced":1},"event":{"id":1,"name":"Event 1","location":"Location 1","dateandtime":"Date and Time 1"},"seats":[{"id":1,"block_id":1,"name":"Seat 1","x0":0,"y0":1,"x1":2,"y1":3,"x2":4,"y2":5,"x3":6,"y3":7}]}',
            (string)$response->getBody());
    }

    public function testGetEventblockActionCallsConverter() {
        $action = new Actions\GetEventblockAction($this->container);

        $request = $this->getGetRequest('/eventblocks/1');
        $response = new \Slim\Http\Response;

        $seatConverterMock = $this->container->get('seatConverter');
        $seatConverterMock->expects($this->once())->method('convert');
        $action($request, $response, [ 'id' => 1 ]);
    }

    public function testCreateEventblockAction() {
        $action = new Actions\CreateEventblockAction($this->container);

        $eventblock = [
            "event_id" => 2,
            "block_id" => 42,
            "category_id" => 24
        ];

        $request = $this->getPostRequest('/eventblocks', $eventblock);
        $response = new \Slim\Http\Response;

        $mapper = $this->container->orm->mapper('Model\Eventblock');

        $numberOfEventblocksBefore = count($mapper->all());
        $action($request, $response, []);
        $numberOfEventblocksAfter = count($mapper->all());
        $this->assertSame($numberOfEventblocksBefore + 1, $numberOfEventblocksAfter);
    }

    public function testDeleteEventblockAction() {
        $action = new Actions\DeleteEventblockAction($this->container);

        $request = $this->getDeleteRequest('/eventblocks/1');
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Eventblock');
        
        $numberOfEventblocksBefore = count($mapper->all());
        $action($request, $response, [ 'id' => 1 ]);
        $numberOfEventblocksAfter = count($mapper->all());
        
        $this->assertSame($numberOfEventblocksBefore - 1, $numberOfEventblocksAfter);
    }
}