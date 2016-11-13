<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class ListOrdersAction {
    private $orm;
    private $reservationConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $orderMapper = $this->orm->mapper('Model\Order');
        $reservationMapper = $this->orm->mapper('Model\Reservation');
        $orders = $orderMapper->all();

        $event_id = $request->getQueryParam('event_id', null);
        $expandedOrders = [];
        foreach ($orders as $order) {
            $reservations = [];
            if ($event_id != null) {
                $reservations = $reservationMapper->where([ 'order_id' => $order->id, 'order_kind' => 'reservation', 'event_id' => $event_id ]);
            } else {
                $reservations = $reservationMapper->where([ 'order_id' => $order->id, 'order_kind' => 'reservation' ]);
            }
            if (count($reservations) > 0) {
                $expandedReservations = $this->reservationConverter->convert($reservations);
                $expandedOrder = new ExpandedOrder($order, $expandedReservations);
                $expandedOrders[] = $expandedOrder;
            }
        }
        
        return $response->withJson($expandedOrders, 200);
    }
}

class CreateOrderAction {
    private $mail;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->mail = $container->get('mail');
        $this->reserver = $container->get('seatReserver');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();

        $order = $this->reserver->order($data['title'], $data['firstname'], $data['lastname'], $data['email'], $data['locale']);

        $totalPrice = 0;
        foreach ($order->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        $this->mail->sendOrderNotification($data['firstname'], $data['lastname'], $data['email'], $order->reservations, $totalPrice);
        $this->mail->sendOrderConfirmation($data['title'], $data['firstname'], $data['lastname'], $data['email'], $data['locale'], $order->reservations, $totalPrice);

        return $response->withJson($order, 201);
    }
}

class ExpandedOrder {
    public $id;
    public $title;
    public $firstname;
    public $lastname;
    public $email;
    public $locale;
    public $timestamp;
    public $reservations;
    public $totalPrice;
    public function __construct($order, $reservations) {
        $this->id = $order->id;
        $this->title = $order->title;
        $this->firstname = $order->firstname;
        $this->lastname = $order->lastname;
        $this->email = $order->email;
        $this->locale = $order->locale;
        $this->timestamp = $order->timestamp;
        $this->reservations = $reservations;

        $this->totalPrice = 0;
        foreach ($reservations as $reservation) {
            $this->totalPrice += $reservation->price; 
        }
    }
}