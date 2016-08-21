<?php

namespace Model;

class Category extends \Spot\Entity {
    protected static $table = 'categories';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'price' => ['type' => 'float', 'required' => true],
            'price_reduced' => ['type' => 'float', 'required' => true]
        ];
    }
}