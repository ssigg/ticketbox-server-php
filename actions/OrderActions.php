<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreateOrderAction {
    private $mail;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->mail = $container->get('mail');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();

        $order = $this->reserver->order($data['firstname'], $data['lastname'], $data['email']);

        $this->mail->sendOrderNotification($data['firstname'], $data['lastname'], $data['email'], [], 0);
        $this->mail->sendOrderConfirmation($order, [], 0);

        return $response->withJson($order, 201);
    }
}