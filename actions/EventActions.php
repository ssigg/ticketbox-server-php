<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class ListEventsAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Event');
        $events = $mapper->all()->toArray();
        return $response->withJson($events, 200);
    }
}

class GetEventAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }
    
    public function __invoke(Request $request, Response $response, $args = []) {
        $eventMapper = $this->orm->mapper('Model\Event');
        $event = $eventMapper->get($args['id']);
        if ($event != null) {
            $eventblockMapper = $this->orm->mapper('Model\Eventblock');
            $eventblocks = $eventblockMapper->where(['event_id' => $event->id]);
            $blocks = [];
            foreach($eventblocks as $eventblock) {
                $blocks[] = $this->convertOneEventblock($eventblock);
            }
            $event->blocks = $blocks;
            return $response->withJson($event, 200);
        } else {
            return $response->withStatus(404);
        }
    }

    private function convertOneEventblock($eventblock) {
        $blockMapper = $this->orm->mapper('Model\Block');
        $categoryMapper = $this->orm->mapper('Model\Category');
        $block = $blockMapper->get($eventblock->get('block_id'));
        $block->seatplan_image_data_url = null;
        $category = $categoryMapper->get($eventblock->get('category_id'));
        $expandedEventblock = new ExpandedEventblock($eventblock->get('id'), $category, $block);
        return $expandedEventblock;
    }
}

class CreateEventAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Event');
        $event = $mapper->create($data);
        return $response->withJson($event, 201);
    }
}

class ChangeEventAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Event');

        $event = $mapper->get($args['id']);
        $event->name = $data['name'];
        $event->location = $data['location'];
        $event->dateandtime = $data['dateandtime'];
        $mapper->update($event);

        return $response->withJson($event, 200);
    }
}

class DeleteEventAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $eventMapper = $this->orm->mapper('Model\Event');
        $eventMapper->delete([ 'id' => $args['id']]);

        $eventblockMapper = $this->orm->mapper('Model\Eventblock');
        $eventblockMapper->delete([ 'event_id' => $args['id']]);

        $reservationMapper = $this->orm->mapper('Model\Reservation');
        $reservationMapper->delete(['event_id' => $args['id']]);
        
        return $response->withJson(200);
    }
}

class ExpandedEventblock {
    public $id;
    public $category;
    public $block;
    public function __construct($id, $category, $block) {
        $this->id = $id;
        $this->category = $category;
        $this->block = $block;
    }
}