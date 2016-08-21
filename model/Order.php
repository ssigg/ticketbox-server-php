<?php

namespace Model;

class Order extends \Spot\Entity {
    protected static $table = 'orders';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'firstname' => ['type' => 'string', 'required' => true],
            'lastname' => ['type' => 'string', 'required' => true],
            'email' => ['type' => 'string', 'required' => true],
            'timestamp' => ['type' => 'integer', 'required' => true]
        ];
    }
}