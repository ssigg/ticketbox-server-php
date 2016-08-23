<?php

namespace Services;

interface ReservationConverterInterface {
    function convert($reservations);
}

class ReservationConverter implements ReservationConverterInterface {
    private $eventMapper;
    private $seatMapper;
    private $eventblockMapper;
    private $categoryMapper;

    public function __construct(\Spot\MapperInterface $eventMapper, \Spot\MapperInterface $seatMapper, \Spot\MapperInterface $eventblockMapper, \Spot\MapperInterface $categoryMapper) {
        $this->eventMapper = $eventMapper;
        $this->seatMapper = $seatMapper;
        $this->eventblockMapper = $eventblockMapper;
        $this->categoryMapper = $categoryMapper;
    }

    public function convert($reservations) {
        $expandedReservations = [];
        foreach ($reservations as $reservation) {
            $eventId = $reservation->get('event_id');
            $event = $this->eventMapper->get($eventId);
            $seat = $this->seatMapper->get($reservation->get('seat_id'));
            $eventblock = $this->eventblockMapper->first([ 'event_id' => $eventId, 'block_id' => $seat->get('block_id') ]);
            $category = $this->categoryMapper->get($eventblock->get('category_id'));
            $isReduced = $reservation->get('is_reduced');
            $price = $isReduced ? $category->get('price_reduced') : $category->get('price');
            $expandedReservations[] = new ExpandedReservation($event, $seat, $category, $isReduced, $price);
        }
        return $expandedReservations;
    }
}

class ExpandedReservation {
    public $event;
    public $seat;
    public $category;
    public $isReduced;
    public $price;

    public function __construct($event, $seat, $category, $isReduced, $price) {
        $this->event = $event;
        $this->seat = $seat;
        $this->category = $category;
        $this->isReduced = $isReduced;
        $this->price = $price;
    }
}