<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class GetBlockAction {
    private $orm;
    private $seatConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->seatConverter = $container->get('seatConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $eventblockMapper = $this->orm->mapper('Model\Eventblock');
        $eventblock = $eventblockMapper->get($args['id']);
        if ($eventblock != null) {
            $blockMapper = $this->orm->mapper('Model\Block');
            $eventblock->block = $blockMapper->get($eventblock->block_id);
            $categoryMapper = $this->orm->mapper('Model\Category');
            $eventblock->category = $categoryMapper->get($eventblock->category_id);
            $eventMapper = $this->orm->mapper('Model\Event');
            $eventblock->event = $eventMapper->get($eventblock->event_id);
            $seatMapper = $this->orm->mapper('Model\Seat');
            $seats = $seatMapper->where(['block_id' => $eventblock->block_id]);
            $convertedSeats = $this->seatConverter->convert($seats, $eventblock);
            $eventblock->seats = $convertedSeats;
            return $response->withJson($eventblock, 200);
        } else {
            return $response->withStatus(404);
        }
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