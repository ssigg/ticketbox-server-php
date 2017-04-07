<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListEventblocksAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Eventblock');
        $eventblocks = $mapper->all();
        return $response->withJson($eventblocks, 200);
    }
}

class GetEventblockAction {
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

class GetMergedEventblockAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->eventblockMerger = $container->get('eventblockMerger');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $key = $args['key'];
        $mergedEventblock = $this->eventblockMerger->getMergedEventblock($key);
        return $response->withJson($mergedEventblock, 200);
    }
}

class CreateEventblockAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Eventblock');
        $eventblock = $mapper->create($data);
        return $response->withJson($eventblock, 201);
    }
}

class DeleteEventblockAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Eventblock');
        $mapper->delete(['id' => $args['id']]);
        return $response->withJson(200);
    }
}