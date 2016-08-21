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
        if ($reservation == null) {
            $state = 'free';
        } else if ($reservation->get('order_id') != null) {
            $state = 'ordered';
        } else if ($reservation->get('token') == $this->tokenProvider->provide()) {
            $state = 'reservedbymyself';
        } else  {
            $state = 'reserved';
        }
        return new SeatWithState($seat, $state);
    }
}

class SeatWithState {
    public $seat;
    public $state;

    public function __construct($seat, $state) {
        $this->seat = $seat;
        $this->state = $state;
    }
}