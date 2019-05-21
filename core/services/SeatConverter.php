<?php

namespace Services;

interface SeatConverterInterface {
    function convert($seats, $eventblock);
}

class SeatConverter implements SeatConverterInterface {
    private $reservationMapper;
    private $tokenProvider;
    private $settings;

    public function __construct(\Spot\MapperInterface $reservationMapper, TokenProviderInterface $tokenProvider, $settings) {
        $this->reservationMapper = $reservationMapper;
        $this->tokenProvider = $tokenProvider;
        $this->settings = $settings;
    }

    public function convert($seats, $eventblock) {
        $oldestLockTime = time() - $this->settings['lifetimeInSeconds'];
        $this->reservationMapper->delete(['timestamp :lt' => $oldestLockTime, 'order_id' => null]);

        $convertedSeats = [];
        foreach ($seats as $seat) {
            $convertedSeats[] = $this->convertOneSeat($seat, $eventblock);
        }
        return $convertedSeats;
    }

    private function convertOneSeat($seat, $eventblock) {
        $reservation = $this->reservationMapper->first([ 'seat_id' => $seat->id, 'event_id' => $eventblock->get('event_id') ]);
        $state = null;
        $reservationId = null;
        if ($reservation == null) {
            $state = 'free';
        } else if ($reservation->get('order_id') != null) {
            if ($reservation->get('order_kind') == 'reservation') {
                $state = 'ordered';
            } else if ($reservation->get('order_kind') == 'boxoffice-purchase') {
                $state = 'sold';
            } else if ($reservation->get('order_kind') == 'customer-purchase') {
                $state = 'sold';
            } else {
                throw new \Exception('Unknown Reservations->order_kind: ' . $reservation->get('order_kind'));
            }
        } else if ($reservation->get('token') == $this->tokenProvider->provide()) {
            $state = 'reservedbymyself';
            $reservationId = $reservation->get('id');
        } else  {
            $state = 'reserved';
        }
        return new SeatWithState($seat, $state, $reservationId);
    }
}

class SeatWithState {
    public $seat;
    public $state;
    public $reservation_id;

    public function __construct($seat, $state, $reservationId) {
        $this->seat = $seat;
        $this->state = $state;
        $this->reservation_id = $reservationId;
    }
}