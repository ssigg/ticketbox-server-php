<?php

class EventActionsTest extends DatabaseTestBase {
    public function testListEventsAction() {
        $action = new Actions\ListEventsAction($this->container);

        $request = $this->getGetRequest('/events');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"name":"Event 1","location":"Location 1","dateandtime":"Date and Time 1"}]',
            (string)$response->getBody());
    }

    public function testGetNotFoundResponse() {
        $action = new Actions\GetEventAction($this->container);

        $request = $this->getGetRequest('/events');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'id' => 'notExisting' ]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testGetEventAction() {
        $action = new Actions\GetEventAction($this->container);

        $request = $this->getGetRequest('/events');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'id' => 1 ]);
        $this->assertSame(
            '{"id":1,"name":"Event 1","location":"Location 1","dateandtime":"Date and Time 1","blocks":[{"id":1,"category":{"id":1,"name":"Category 1","price":2,"price_reduced":1},"block":{"id":1,"seatplan_image_data_url":null,"name":"Block 1"}}]}',
            (string)$response->getBody());
    }

    public function testCreateEventAction() {
        $action = new Actions\CreateEventAction($this->container);

        $request = $this->getPostRequest('/events', [ 'name' => 'Test name', 'location' => 'There', 'dateandtime' => 'Tomorrow, 8 PM' ]);
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
        $newDateandtime = "New date and time";
        $data = [
            "name" => $newName,
            "location" => $newLocation,
            "dateandtime" => $newDateandtime
        ];
        $request = $this->getPutRequest('/events/1', $data);
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Event');
        
        $eventBefore = $mapper->get(1);
        $this->assertNotSame($eventBefore->name, $newName);
        $this->assertNotSame($eventBefore->location, $newLocation);
        $this->assertNotSame($eventBefore->dateandtime, $newDateandtime);

        $response = $action($request, $response, [ 'id' => 1 ]);

        $eventAfter = $mapper->get(1);
        $this->assertSame($eventAfter->name, $newName);
        $this->assertSame($eventAfter->location, $newLocation);
        $this->assertSame($eventAfter->dateandtime, $newDateandtime);

        $this->assertSame(
            '{"id":1,"name":"New name","location":"New location","dateandtime":"New date and time"}',
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