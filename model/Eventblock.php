<?php

namespace Model;

class Eventblock extends \Spot\Entity {
    protected static $table = 'eventblocks';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'event_id' => ['type' => 'integer', 'required' => true],
            'block_id' => ['type' => 'integer', 'required' => true],
            'category_id' => ['type' => 'integer', 'required' => true]
        ];
    }
}