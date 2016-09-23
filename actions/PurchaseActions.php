<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CreateBoxofficePurchaseAction {
    private $mail;
    private $reserver;
    private $boxofficeSettings;

    public function __construct(ContainerInterface $container) {
        $this->mail = $container->get('mail');
        $this->reserver = $container->get('seatReserver');
        $this->boxofficeSettings = $container->get('settings')['boxoffice'];
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $purchase = $this->reserver->boxofficePurchase($this->boxofficeSettings['name']);

        $totalPrice = 0;
        foreach ($purchase->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        $this->mail->sendBoxofficePurchaseNotification($this->boxofficeSettings['name'], $purchase->reservations, $totalPrice);

        return $response->withJson($purchase, 201);
    }
}