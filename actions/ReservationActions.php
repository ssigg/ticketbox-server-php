<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListMyReservationsAction {
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

class ListAllReservationsAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Reservation');
        $reservations = $mapper->where([ 'order_id <>' => null ]);
        return $response->withJson($reservations, 200);
    }
}

class CreateReservationAction {
    private $orm;
    private $reserver;
    private $reservationConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reserver = $container->get('seatReserver');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $seat = $this->orm->mapper('Model\Seat')->get($data['seat_id']);
        $event = $this->orm->mapper('Model\Event')->get($data['event_id']);
        $category = $this->orm->mapper('Model\Category')->get($data['category_id']);
        $reservation = $this->reserver->reserve($seat, $event, $category);
        if ($reservation != null) {
            $expandedReservation = $this->reservationConverter->convert([ $reservation ])[0];
            return $response->withJson($expandedReservation, 201);
        } else {
            return $response->withStatus(409);
        }
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
    private $reservationConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reserver = $container->get('seatReserver');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $reductionValue = $data['isReduced'];
        $reservation = $this->reserver->changeReduction($args['id'], $reductionValue);
        $expandedReservation = $this->reservationConverter->convert([ $reservation ])[0];
        return $response->withJson($expandedReservation, 200);
    }
}