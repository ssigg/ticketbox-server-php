<?php

namespace Model;

class CustomerPurchase extends \Spot\Entity {
    protected static $table = 'customer_purchases';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title' => ['type' => 'string', 'required' => true],
            'firstname' => ['type' => 'string', 'required' => true],
            'lastname' => ['type' => 'string', 'required' => true],
            'email' => ['type' => 'string', 'required' => true],
            'price' => ['type' => 'float', 'required' => true],
            'locale' => ['type' => 'string', 'required' => true],
            'timestamp' => ['type' => 'integer', 'required' => true]
        ];
    }
}