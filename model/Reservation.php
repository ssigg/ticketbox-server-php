<?php

namespace Model;

class Reservation extends \Spot\Entity {
    protected static $table = 'reservations';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'token' => ['type' => 'string', 'required' => true],
            'seat_id' => ['type' => 'integer', 'required' => true, 'unique' => 'eventseat'],
            'event_id' => ['type' => 'integer', 'required' => true, 'unique' => 'eventseat'],
            'is_reduced' => ['type' => 'boolean', 'required' => true],
            'timestamp' => ['type' => 'integer', 'required' => true],
            'order_id' => ['type' => 'integer', 'required' => false],
            'is_sold' => ['type' => 'boolean', 'required' => true]
        ];
    }
} 