<?php

namespace Services;

interface SeatReserverInterface {
    function getReservations();
    function reserve($seats, $event);
    function release($reservationId);
    function changeReduction($reservationId, $value);
    function order($title, $firstname, $lastname, $email);
    function boxofficePurchase($boxofficeName);
}

class SeatReserver implements SeatReserverInterface {
    private $orderMapper;
    private $boxofficePurchaseMapper;
    private $reservationMapper;
    private $reservationConverter;
    private $token;
    private $settings;
    
    public function __construct(
        \Spot\MapperInterface $orderMapper,
        \Spot\MapperInterface $boxofficePurchaseMapper,
        \Spot\MapperInterface $reservationMapper,
        ReservationConverterInterface $reservationConverter,
        TokenProviderInterface $tokenProvider,
        $settings) {
            $this->orderMapper = $orderMapper;
            $this->boxofficePurchaseMapper = $boxofficePurchaseMapper;
            $this->reservationMapper = $reservationMapper;
            $this->reservationConverter = $reservationConverter;
            $this->token = $tokenProvider->provide();
            $this->settings = $settings;
    }

    public function getReservations() {
        $this->deleteStaleReservations();
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        return $expandedReservations;
    }

    public function reserve($seat, $event) {
        $this->deleteStaleReservations();
        $data = [
            'token' => $this->token,
            'seat_id' => $seat->get('id'),
            'event_id' => $event->get('id'),
            'timestamp' => time(),
            'is_reduced' => false
        ];
        $reservation = $this->reservationMapper->create($data);
        return $reservation;
    }

    public function release($reservationId) {
        $this->reservationMapper->delete([ 'id' => $reservationId, 'token' => $this->token ]);
    }

    public function changeReduction($reservationId, $value) {
        $this->deleteStaleReservations();
        $reservation = $this->reservationMapper->first([ 'id' => $reservationId, 'token' => $this->token, 'order_id' => null]);
        if ($reservation != null) {
            $reservation->is_reduced = $value;
            $this->reservationMapper->update($reservation);
        }
        return $reservation;
    }

    public function order($title, $firstname, $lastname, $email) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $data = [
                'title' => $title,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'timestamp' => time()
            ];
            $order = $this->orderMapper->create($data);
            $order->reservations = $this->reservationConverter->convert($reservations);
            foreach ($reservations as $reservation) {
                $reservation->order_kind = 'reservation';
                $reservation->order_id = $order->get('id');
                $this->reservationMapper->update($reservation);
            }
            return $order;
        }
        return null;
    }

    public function boxofficePurchase($boxofficeName) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $data = [
                'boxoffice' => $boxofficeName,
                'timestamp' => time()
            ];
            $purchase = $this->boxofficePurchaseMapper->create($data);
            $purchase->reservations = $this->reservationConverter->convert($reservations);
            foreach ($reservations as $reservation) {
                $reservation->order_kind = 'boxoffice-purchase';
                $reservation->order_id = $purchase->get('id');
                $this->reservationMapper->update($reservation);
            }
            return $purchase;
        }
        return null;
    }

    private function deleteStaleReservations() {
        $oldestLockTime = time() - $this->settings['lifetimeInSeconds'];
        $this->reservationMapper->delete(['timestamp :lt' => $oldestLockTime, 'order_id' => null]);
    }
}