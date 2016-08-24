<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListReservationsAction {
    private $orm;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $reservations = $this->reserver->getReservations();
        return $response->withJson($reservations, 200);
    }
}

class CreateReservationAction {
    private $orm;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $seat = $this->orm->mapper('Model\Seat')->get($data['seatId']);
        $event = $this->orm->mapper('Model\Event')->get($data['eventId']);
        $reservation = $this->reserver->reserve($seat, $event);
        return $response->withJson($reservation, 201);
    }
}

class DeleteReservationAction {
    private $orm;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $this->reserver->release($args['id']);
        return $response->withJson(200);
    }
}

class ChangeReductionForReservationAction {
    private $orm;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $reductionValue = $data['isReduced'];
        $reservation = $this->reserver->changeReduction($args['id'], $reductionValue);
        return $response->withJson($reservation, 200);
    }
}