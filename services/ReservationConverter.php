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

    public function __construct(\Spot\MapperInterface $eventMapper, \Spot\MapperInterface $seatMapper, \Spot\MapperInterface $eventblockMapper, \Spot\MapperInterface $categoryMapper, $priceModificators) {
        $this->eventMapper = $eventMapper;
        $this->seatMapper = $seatMapper;
        $this->eventblockMapper = $eventblockMapper;
        $this->categoryMapper = $categoryMapper;
        $this->priceModificators = $priceModificators;
    }

    public function convert($reservations) {
        $expandedReservations = [];
        foreach ($reservations as $reservation) {
            $id = $reservation->get('id');
            $unique_id = $reservation->get('unique_id');
            $eventId = $reservation->get('event_id');
            $event = $this->eventMapper->get($eventId);
            $seat = $this->seatMapper->get($reservation->get('seat_id'));
            $category = $this->categoryMapper->get($reservation->get('category_id'));
            $isReduced = $reservation->get('is_reduced');
            $price = $isReduced ? $category->get('price_reduced') : $category->get('price');
            $modifiedPrice = ($price * $this->priceModificators['factor']) + $this->priceModificators['addend'];
            $expandedReservations[] = new ExpandedReservation($id, $unique_id, $event, $seat, $category, $isReduced, $modifiedPrice);
        }
        return $expandedReservations;
    }
}