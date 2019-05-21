<?php

namespace Model;

class Reservation extends \Spot\Entity {
    protected static $table = 'reservations';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'unique_id' => ['type' => 'string', 'required' => true, 'unique' => 'unique_id'],
            'token' => ['type' => 'string', 'required' => true],
            'seat_id' => ['type' => 'integer', 'required' => true, 'unique' => 'eventseat'],
            'event_id' => ['type' => 'integer', 'required' => true, 'unique' => 'eventseat'],
            'category_id' => ['type' => 'integer', 'required' => true],
            'is_reduced' => ['type' => 'boolean', 'required' => true],
            'order_id' => ['type' => 'integer', 'required' => false],
            'order_kind' => ['type' => 'string', 'required' => false],
            'is_scanned' => ['type' => 'boolean', 'required' => true],
            'timestamp' => ['type' => 'integer', 'required' => true]
        ];
    }

    public static function events(\Spot\EventEmitter $eventEmitter) {
        $eventEmitter->on('afterValidate', function (\Spot\Entity $reservation, \Spot\Mapper $mapper) {
            if ($reservation->order_kind == null) {
                return true;
            } else if ($reservation->order_kind == 'reservation') {
                return true;
            } else if ($reservation->order_kind == 'boxoffice-purchase') {
                return true;
            } else if ($reservation->order_kind == 'customer-purchase') {
                return true;
            } else {
                return false;
            }
        });
    }
} 