<?php

class BlockActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
    }

    public function testListBlocksAction() {
        $action = new Actions\ListBlocksAction($this->container);

        $request = $this->getGetRequest('/blocks');
        $response = new \Slim\Http\Response;

        $response = $action($request, $response, []);
        $this->assertSame(
            '[{"id":1,"seatplan_image_data_url":null,"name":"Block 1"}]',
            (string)$response->getBody());
    }

    public function testGetBlockAction() {
        $action = new Actions\GetBlockAction($this->container);

        $request = $this->getGetRequest('/blocks/1');
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Block');
        
        $response = $action($request, $response, [ 'id' => 1 ]);
        
        $this->assertSame(
            '{"id":1,"seatplan_image_data_url":"data_url","name":"Block 1"}',
            (string)$response->getBody());
    }

    public function testCreateBlockAction() {
        $action = new Actions\CreateBlockAction($this->container);

        $request = $this->getPostRequest('/blocks', [ 'name' => 'Test name', 'seatplan_image_data_url' => 'dataurl' ]);
        $response = new \Slim\Http\Response();

        $blockMapper = $this->container->orm->mapper('Model\Block');

        $numberOfBlocksBefore = count($blockMapper->all());
        $action($request, $response, []);
        $numberOfBlocksAfter = count($blockMapper->all());
        
        $this->assertSame($numberOfBlocksBefore + 1, $numberOfBlocksAfter);
    }

    public function testChangeBlockAction() {
        $action = new Actions\ChangeBlockAction($this->container);

        $newName = "New name";
        $newSeatplanImageDataUrl = "New dataurl";
        $data = [
            "name" => $newName,
            "seatplan_image_data_url" => $newSeatplanImageDataUrl
        ];
        $request = $this->getPutRequest('/blocks/1', $data);
        $response = new \Slim\Http\Response();

        $mapper = $this->container->orm->mapper('Model\Block');
        
        $blockBefore = $mapper->get(1);
        $this->assertNotSame($blockBefore->name, $newName);
        $this->assertNotSame($blockBefore->seatplan_image_data_url, $newSeatplanImageDataUrl);

        $response = $action($request, $response, [ 'id' => 1 ]);

        $blockAfter = $mapper->get(1);
        $this->assertSame($blockAfter->name, $newName);
        $this->assertSame($blockAfter->seatplan_image_data_url, $newSeatplanImageDataUrl);

        $this->assertSame(
            '{"id":1,"seatplan_image_data_url":"New dataurl","name":"New name"}',
            (string)$response->getBody());
    }

    public function testDeleteBlockAction() {
        $action = new Actions\DeleteBlockAction($this->container);

        $request = $this->getDeleteRequest('/blocks/1');
        $response = new \Slim\Http\Response();

        $blockMapper = $this->container->orm->mapper('Model\Block');
        $eventblockMapper = $this->container->orm->mapper('Model\Eventblock');
        $seatMapper = $this->container->orm->mapper('Model\Seat');
        $reservationMapper = $this->container->orm->mapper('Model\Reservation');

        $numberOfBlocksBefore = count($blockMapper->all());
        $numberOfEventblocksBefore = count($eventblockMapper->all());
        $numberOfSeatsBefore = count($seatMapper->all());
        $numberOfReservationsBefore = count($reservationMapper->all());
        $action($request, $response, [ 'id' => 1 ]);
        $numberOfBlocksAfter = count($blockMapper->all());
        $numberOfEventblocksAfter = count($eventblockMapper->all());
        $numberOfSeatsAfter = count($seatMapper->all());
        $numberOfReservationsAfter = count($reservationMapper->all());
        
        $this->assertSame($numberOfBlocksBefore - 1, $numberOfBlocksAfter);
        $this->assertSame($numberOfEventblocksBefore - 1, $numberOfEventblocksAfter);
        $this->assertSame($numberOfSeatsBefore - 1, $numberOfSeatsAfter);
        $this->assertSame($numberOfReservationsBefore - 1, $numberOfReservationsAfter);
    }
}