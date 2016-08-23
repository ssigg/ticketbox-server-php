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
            $eventBlocks = $eventblockMapper->select('id')->where(['event_id' => $event->id]);
            $event->blocks = $eventBlocks->toArray();
            return $response->withJson($event, 200);
        } else {
            return $response->withStatus(404);
        }
    }
}