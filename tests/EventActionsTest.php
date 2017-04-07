<?php

class EventActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        $eventblockMergerMock = $this->getMockBuilder(EventblockMergerInterface::class)
            ->setMethods(['merge'])
            ->getMock();
        $this->container['eventblockMerger'] = $eventblockMergerMock;
    }

    public function testListAllEventsAction() {
        $action = new Actions\ListAllEventsAction($this->container);

        $request = $this->getGetRequest('/events');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"name":"Event 1","location":"Location 1","location_address":null,"location_directions_public_transport":null,"location_directions_car":null,"dateandtime":"Date and Time 1","visible":true},{"id":2,"name":"Event 2","location":"Location 2","location_address":null,"location_directions_public_transport":null,"location_directions_car":null,"dateandtime":"Date and Time 2","visible":false}]',
            (string)$response->getBody());
    }

    public function testListVisibleEventsAction() {
        $action = new Actions\ListVisibleEventsAction($this->container);

        $request = $this->getGetRequest('/events');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"name":"Event 1","location":"Location 1","location_address":null,"location_directions_public_transport":null,"location_directions_car":null,"dateandtime":"Date and Time 1","visible":true}]',
            (string)$response->getBody());
    }

    public function testGetNotExistingEventResponse() {
        $action = new Actions\GetEventAction($this->container);

        $request = $this->getGetRequest('/events/notExisting');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'id' => 'notExisting' ]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetEventAction() {
        $action = new Actions\GetEventAction($this->container);

        $request = $this->getGetRequest('/events/1');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'id' => 1 ]);
        $this->assertSame(
            '{"id":1,"name":"Event 1","location":"Location 1","location_address":null,"location_directions_public_transport":null,"location_directions_car":null,"dateandtime":"Date and Time 1","visible":true,"blocks":[{"id":1,"category":{"id":1,"name":"Category 1","color":"#000","price":2,"price_reduced":1},"block":{"id":1,"seatplan_image_data_url":null,"name":"Block 1"}}]}',
            (string)$response->getBody());
    }

    public function testGetNotExistingEventWithMergedEventblocksResponse() {
        $action = new Actions\GetEventWithMergedEventblocksAction($this->container);

        $request = $this->getGetRequest('/events/notExisting');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'id' => 'notExisting' ]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetEventWithMergedEventblocksAction() {
        $eventblockMergerMock = $this->container['eventblockMerger'];
        $eventblockMergerMock
            ->method('merge')
            ->willReturn('mergedEventblocks');

        $action = new Actions\GetEventWithMergedEventblocksAction($this->container);

        $request = $this->getGetRequest('/events/1');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'id' => 1 ]);
        $this->assertSame(
            '{"id":1,"name":"Event 1","location":"Location 1","location_address":null,"location_directions_public_transport":null,"location_directions_car":null,"dateandtime":"Date and Time 1","visible":true,"blocks":"mergedEventblocks"}',
            (string)$response->getBody());
    }

    public function testCreateEventAction() {
        $action = new Actions\CreateEventAction($this->container);

        $request = $this->getPostRequest('/events', [ 'name' => 'Test name', 'location' => 'There', 'dateandtime' => 'Tomorrow, 8 PM', 'visible' => true ]);
        $response = new \Slim\Http\Response();

        $eventMapper = $this->container->orm->mapper('Model\Event');

        $numberOfEventsBefore = count($eventMapper->all());
        $action($request, $response, []);
        $numberOfEventsAfter = count($eventMapper->all());
        
        $this->assertSame($numberOfEventsBefore + 1, $numberOfEventsAfter);
    }

    public function testChangeEventAction() {
        $action = new Actions\ChangeEventAction($this->container);

        $newName = "New name";
        $newLocation = "New location";
        $newLocationAddress = "New location address";
        $newLocationDirectionsPublicTransport = "New location directions public transport";
        $newLocationDirectionsCar = "New location directions car";
        $newDateandtime = "New date and time";
        $newVisible = false;
        $data = [
            "name" => $newName,
            "location" => $newLocation,
            "location_address" => $newLocationAddress,
            "location_directions_public_transport" => $newLocationDirectionsPublicTransport,
            "location_directions_car" => $newLocationDirectionsCar,
            "dateandtime" => $newDateandtime,
            "visible" => $newVisible
        ];
        $request = $this->getPutRequest('/events/1', $data);
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Event');
        
        $eventBefore = $mapper->get(1);
        $this->assertNotSame($eventBefore->name, $newName);
        $this->assertNotSame($eventBefore->location, $newLocation);
        $this->assertNotSame($eventBefore->location_address, $newLocationAddress);
        $this->assertNotSame($eventBefore->location_directions_public_transport, $newLocationDirectionsPublicTransport);
        $this->assertNotSame($eventBefore->location_directions_car, $newLocationDirectionsCar);
        $this->assertNotSame($eventBefore->dateandtime, $newDateandtime);
        $this->assertNotSame($eventBefore->visible, $newVisible);

        $response = $action($request, $response, [ 'id' => 1 ]);

        $eventAfter = $mapper->get(1);
        $this->assertSame($eventAfter->name, $newName);
        $this->assertSame($eventAfter->location, $newLocation);
        $this->assertSame($eventAfter->location_address, $newLocationAddress);
        $this->assertSame($eventAfter->location_directions_public_transport, $newLocationDirectionsPublicTransport);
        $this->assertSame($eventAfter->location_directions_car, $newLocationDirectionsCar);
        $this->assertSame($eventAfter->dateandtime, $newDateandtime);
        $this->assertSame($eventAfter->visible, $newVisible);

        $this->assertSame(
            '{"id":1,"name":"New name","location":"New location","location_address":"New location address","location_directions_public_transport":"New location directions public transport","location_directions_car":"New location directions car","dateandtime":"New date and time","visible":false}',
            (string)$response->getBody());
    }

    public function testDeleteEventAction() {
        $action = new Actions\DeleteEventAction($this->container);

        $request = $this->getDeleteRequest('/events/1');
        $response = new \Slim\Http\Response();

        $eventMapper = $this->container->orm->mapper('Model\Event');
        $eventblockMapper = $this->container->orm->mapper('Model\Eventblock');
        $reservationMapper = $this->container->orm->mapper('Model\Reservation');

        $numberOfEventsBefore = count($eventMapper->all());
        $numberOfEventblocksBefore = count($eventblockMapper->all());
        $numberOfReservationsBefore = count($reservationMapper->all());
        $action($request, $response, [ 'id' => 1 ]);
        $numberOfEventsAfter = count($eventMapper->all());
        $numberOfEventblocksAfter = count($eventblockMapper->all());
        $numberOfReservationsAfter = count($reservationMapper->all());
        
        $this->assertSame($numberOfEventsBefore - 1, $numberOfEventsAfter);
        $this->assertSame($numberOfEventblocksBefore - 1, $numberOfEventblocksAfter);
        $this->assertSame($numberOfReservationsBefore - 1, $numberOfReservationsAfter);
    }
}