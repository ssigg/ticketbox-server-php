<?php

namespace Model;

class BoxofficePurchase extends \Spot\Entity {
    protected static $table = 'boxoffice_purchases';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'unique_id' => ['type' => 'string', 'required' => true],
            'boxoffice' => ['type' => 'string', 'required' => true],
            'price' => ['type' => 'float', 'required' => true],
            'locale' => ['type' => 'string', 'required' => true],
            'is_printed' => ['type' => 'boolean', 'required' => true],
            'timestamp' => ['type' => 'integer', 'required' => true]
        ];
    }
}