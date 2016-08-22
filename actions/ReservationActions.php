<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreateReservationAction {
    private $db;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->db = $container->get('database');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $seats = $this->db->mapper('Model\Seat')->where([ 'id' => $data['seatId'] ]);
        $event = $this->db->mapper('Model\Event')->first([ 'id' => $data['eventId'] ]);
        $this->reserver->reserve($seats, $event);
        return $response->withJson(201);
    }
}

class DeleteReservationAction {
    private $db;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->db = $container->get('database');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $seats = $this->db->mapper('Model\Seat')->where([ 'id' => $args['seatId'] ]);
        $event = $this->db->mapper('Model\Event')->first([ 'id' => $args['eventId'] ]);
        $this->reserver->release($seats, $event);
        return $response->withJson(200);
    }
}

class ChangeReductionForReservationAction {
    private $db;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->db = $container->get('database');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $seat = $this->db->mapper('Model\Seat')->first([ 'id' => $args['seatId'] ]);
        $event = $this->db->mapper('Model\Event')->first([ 'id' => $args['eventId'] ]);
        $data = $request->getParsedBody();
        $reductionValue = $data['isReduced'];
        $this->reserver->changeReduction($seat, $event, $reductionValue);
        return $response->withJson(200);
    }
}