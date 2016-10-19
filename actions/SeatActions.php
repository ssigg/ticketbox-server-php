<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ListSeatsAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Seat');
        $params = $request->getQueryParams();
        $seats = [];
        if (array_key_exists('block_id', $params)) {
            $seats = $mapper->where([ 'block_id' => $params['block_id'] ]);
        } else {
            $seats = $mapper->all();
        }
        return $response->withJson($seats, 200);
    }
}

class CreateSeatsAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $mapper = $this->orm->mapper('Model\Seat');
        $seats = [];
        foreach ($data as $seatData) {
            $seat = $mapper->create($seatData);
            $seats[] = $seat;
        }
        return $response->withJson($seats, 201);
    }
}

class DeleteSeatAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $reservationMapper = $this->orm->mapper('Model\Reservation');
        $reservationMapper->delete([ 'seat_id' => $args['id'] ]);

        $seatMapper = $this->orm->mapper('Model\Seat');
        $seatMapper->delete([ 'id' => $args['id'] ]);

        return $response->withJson(200);
    }
}