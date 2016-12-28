<?php

namespace Services;

interface ExpandedReservationInterface { }

class ExpandedReservation implements ExpandedReservationInterface {
    public $id;
    public $unique_id;
    public $event;
    public $seat;
    public $category;
    public $isReduced;
    public $price;

    public function __construct($id, $unique_id, $event, $seat, $category, $isReduced, $price) {
        $this->id = $id;
        $this->unique_id = $unique_id;
        $this->event = $event;
        $this->seat = $seat;
        $this->category = $category;
        $this->isReduced = $isReduced;
        $this->price = $price;
    }
}