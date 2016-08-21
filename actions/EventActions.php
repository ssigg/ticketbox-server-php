<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class ListEventsAction {
    private $db;

    public function __construct(ContainerInterface $container) {
        $this->db = $container->get('database');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->db->mapper('Model\Event');
        $events = $mapper->all()->toArray();
        return $response->withJson($events, 200);
    }
}

class GetEventAction {
    private $db;

    public function __construct(ContainerInterface $container) {
        $this->db = $container->get('database');
    }
    
    public function __invoke(Request $request, Response $response, $args = []) {
        $eventMapper = $this->db->mapper('Model\Event');
        $event = $eventMapper->get($args['id']);
        if ($event != null) {
            $eventblockMapper = $this->db->mapper('Model\Eventblock');
            $eventBlocks = $eventblockMapper->select('id')->where(['event_id' => $event->id]);
            $event->blocks = $eventBlocks->toArray();
            return $response->withJson($event, 200);
        } else {
            return $response->withStatus(404);
        }
    }
}