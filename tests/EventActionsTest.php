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
}