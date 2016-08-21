<?php

namespace Model;

class Block extends \Spot\Entity {
    protected static $table = 'blocks';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'seatplan_image_data_url' => ['type' => 'text', 'required' => true],
            'name' => ['type' => 'string', 'required' => true]
        ];
    }
}