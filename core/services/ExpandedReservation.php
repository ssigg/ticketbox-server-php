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
    public $order_id;

    public function __construct($id, $unique_id, $event, $seat, $category, $isReduced, $price, $order_id = null) {
        $this->id = $id;
        $this->unique_id = $unique_id;
        $this->event = $event;
        $this->seat = $seat;
        $this->category = $category;
        $this->isReduced = $isReduced;
        $this->price = $price;
        $this->order_id = $order_id;
    }
}