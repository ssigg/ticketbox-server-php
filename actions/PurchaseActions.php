<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class ListBoxofficePurchasesAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $boxofficePurchaseMapper = $this->orm->mapper('Model\BoxofficePurchase');
        $reservationMapper = $this->orm->mapper('Model\Reservation');

        $boxofficePurchases = $boxofficePurchaseMapper->all();

        $eventId = $request->getQueryParam('event_id', null);
        $expandedBoxofficePurchases = [];
        foreach ($boxofficePurchases as $boxofficePurchase) {
            $reservations = [];
            if ($eventId != null) {
                $reservations = $reservationMapper->where([ 'order_id' => $boxofficePurchase->id, 'order_kind' => 'boxoffice-purchase', 'event_id' => $eventId ]);
            } else {
                $reservations = $reservationMapper->where([ 'order_id' => $boxofficePurchase->id, 'order_kind' => 'boxoffice-purchase' ]);
            }
            if (count($reservations) > 0) {
                $expandedReservations = $this->reservationConverter->convert($reservations);
                $expandedBoxofficePurchase = new ExpandedBoxofficePurchase($boxofficePurchase, $expandedReservations);
                $expandedBoxofficePurchases[] = $expandedBoxofficePurchase;
            }
        }

        return $response->withJson($expandedBoxofficePurchases, 200);
    }
}

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
        $data = $request->getParsedBody();
        $boxofficeName = $data['boxofficeName'];
        $boxofficeType = $data['boxofficeType']; // [paper|pdf]
        $customerEmail = isset($data['email']) ? $data['email'] : null; 
        
        $purchase = $this->reserver->boxofficePurchase($this->boxofficeSettings['name'], $data['locale']);

        $totalPrice = 0;
        foreach ($purchase->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        $this->mail->sendBoxofficePurchaseNotification($boxofficeName, $purchase->reservations, $totalPrice);

        return $response->withJson($purchase, 201);
    }
}

class ExpandedBoxofficePurchase {
    public $id;
    public $boxoffice;
    public $locale;
    public $timestamp;
    public $reservations;
    public $totalPrice;
    public function __construct($boxofficePurchase, $reservations) {
        $this->id = $boxofficePurchase->id;
        $this->boxoffice = $boxofficePurchase->boxoffice;
        $this->locale = $boxofficePurchase->locale;
        $this->timestamp = $boxofficePurchase->timestamp;
        $this->reservations = $reservations;

        $this->totalPrice = 0;
        foreach ($reservations as $reservation) {
            $this->totalPrice += $reservation->price; 
        }
    }
}