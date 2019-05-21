<?php

namespace Model;

class Seat extends \Spot\Entity {
    protected static $table = 'seats';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'block_id' => ['type' => 'integer', 'required' => true],
            'name' => ['type' => 'string', 'required' => true],
            'x0' => ['type' => 'float', 'required' => false],
            'y0' => ['type' => 'float', 'required' => false],
            'x1' => ['type' => 'float', 'required' => false],
            'y1' => ['type' => 'float', 'required' => false],
            'x2' => ['type' => 'float', 'required' => false],
            'y2' => ['type' => 'float', 'required' => false],
            'x3' => ['type' => 'float', 'required' => false],
            'y3' => ['type' => 'float', 'required' => false],
        ];
    }
}
