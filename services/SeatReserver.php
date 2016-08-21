<?php

namespace Services;

interface SeatReserverInterface {
    function reserve($seats, $event);
    function release($seats);
    function addReduction($seat);
    function removeReduction($seat);
    function order($firstname, $lastname, $email);
}

class SeatReserver implements SeatReserverInterface {
    private $orderMapper;
    private $reservationMapper;
    private $token;
    private $settings;
    
    public function __construct(\Spot\MapperInterface $orderMapper, \Spot\MapperInterface $reservationMapper, TokenProviderInterface $tokenProvider, $settings) {
        $this->orderMapper = $orderMapper;
        $this->reservationMapper = $reservationMapper;
        $this->token = $tokenProvider->provide();
        $this->settings = $settings;
    }

    public function getReservedSeats() {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        $seats = [];
        foreach ($reservations as $reservation) {
            $seats[] = $this->seatMapper->first([ 'id' => $reservation->seat_id ]);
        }
        return $seats;
    }

    public function reserve($seats, $event) {
        $oldestLockTime = time() - $this->settings['lifetimeInSeconds'];
        $this->reservationMapper->delete(['timestamp :lt' => $oldestLockTime, 'order_id' => null]);
        foreach ($seats as $seat) {
            $data = [
                'token' => $this->token,
                'seat_id' => $seat->get('id'),
                'event_id' => $event->get('id'),
                'timestamp' => time(),
                'is_reduced' => false,
                'is_sold' => false
            ];
            $this->reservationMapper->create($data);
        }
    }

    public function release($seats) {
        foreach ($seats as $seat) {
            $this->reservationMapper->delete([ 'seat_id' => $seat->id, 'token' => $this->token ]);
        }
    }

    public function addReduction($seat) {
        $this->changeReductionTo($seat, true);
    }

    public function removeReduction($seat) {
        $this->changeReductionTo($seat, false);
    }

    public function order($firstname, $lastname, $email) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $data = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'timestamp' => time()
            ];
            $order = $this->orderMapper->create($data);
            foreach ($reservations as $reservation) {
                $reservation->order_id = $order->get('id');
                $this->reservationMapper->update($reservation);
            }
            return $order;
        }
        return null;
    }

    private function changeReductionTo($seat, $value) {
        $reservation = $this->reservationMapper->first([ 'seat_id' => $seat->get('id'), 'token' => $this->token ]);
        if ($reservation != null) {
            $reservation->is_reduced = $value;
            $this->reservationMapper->update($reservation);
        }
    }
}