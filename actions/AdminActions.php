<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MigrateAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mappers = [
            $this->orm->mapper('Model\Block'),
            $this->orm->mapper('Model\BoxofficePurchase'),
            $this->orm->mapper('Model\Category'),
            $this->orm->mapper('Model\Event'),
            $this->orm->mapper('Model\Eventblock'),
            $this->orm->mapper('Model\Order'),
            $this->orm->mapper('Model\Reservation'),
            $this->orm->mapper('Model\Seat'),
        ];
        foreach ($mappers as $mapper) {
            $mapper->migrate();
        }
        return $response->withStatus(200);
    }
}