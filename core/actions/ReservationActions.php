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

class CreateUnspecifiedReservationsAction {
    private $orm;
    private $seatConverter;
    private $reserver;
    private $reservationConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->seatConverter = $container->get('seatConverter');
        $this->reserver = $container->get('seatReserver');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();

        $eventblock = $this->orm->mapper('Model\Eventblock')->get($data['eventblock_id']);
        $numberOfSeats = $data['number_of_seats'];
        
        $reservations = $this->reserveSeats($eventblock, $numberOfSeats);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        return $response->withJson($expandedReservations, 201);
    }

    private function reserveSeats($eventblock, $numberOfSeats) {
        $event = $this->orm->mapper('Model\Event')->get($eventblock->get('event_id'));
        $category = $this->orm->mapper('Model\Category')->get($eventblock->get('category_id'));
        $allSeats = $this->orm->mapper('Model\Seat')->where([ 'block_id' => $eventblock->get('block_id') ]);
        $reservations = [];
        foreach ($allSeats as $seat) {
            $convertedSeat = $this->seatConverter->convert([ $seat ], $eventblock)[0];
            if ($convertedSeat->state == 'free') {
                $reservation = $this->reserver->reserve($seat, $event, $category);
                if ($reservation != null) {
                    $reservations[] = $reservation;
                }
            }
            if (count($reservations) == $numberOfSeats) {
                return $reservations;
            }
        }
        return $reservations;
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

class GetReservationsExpirationTimestampAction {
    private $reserver;
    
    public function __construct(ContainerInterface $container) {
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $expirationTimestamp = $this->reserver->getReservationsExpirationTimestamp();
        $expirationTimestampContainer = [
            'value' => $expirationTimestamp
        ];
        return $response->withJson($expirationTimestampContainer, 200);
    }
}