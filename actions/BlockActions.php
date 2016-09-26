<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListBlocksAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Block');
        $blocks = $mapper->all();
        return $response->withJson($blocks, 200);
    }
}

class CreateBlockAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Block');
        $block = $mapper->create($data);
        return $response->withJson($block, 201);
    }
}

class ChangeBlockAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Block');

        $block = $mapper->get($args['id']);
        $block->seatplan_image_data_url = $data['seatplan_image_data_url'];
        $block->name = $data['name'];
        $mapper->update($block);

        return $response->withJson($block, 200);
    }
}

class DeleteBlockAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $blockMapper = $this->orm->mapper('Model\Block');
        $eventblockMapper = $this->orm->mapper('Model\Eventblock');
        $seatMapper = $this->orm->mapper('Model\Seat');
        $reservationMapper = $this->orm->mapper('Model\Reservation');

        $seatIds = $this->getSeatIds($seatMapper, $args['id']);
        
        $reservationMapper->delete(['seat_id' => $seatIds]);
        $seatMapper->delete(['block_id' => $args['id']]);
        $eventblockMapper->delete(['block_id' => $args['id']]);
        $blockMapper->delete(['id' => $args['id']]);

        return $response->withJson(200);
    }

    private function getSeatIds(\Spot\MapperInterface $seatMapper, $blockId) {
        $seats = $seatMapper->where(['block_id' => $blockId]);
        $seatIds = [];
        foreach ($seats as $seat) {
            $seatIds[] = $seat->id;
        }
        return $seatIds;
    }
}