<?php

namespace Model;

class Event extends \Spot\Entity {
    protected static $table = 'events';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'location' => ['type' => 'string', 'required' => false],
            'dateandtime' => ['type' => 'string', 'required' => false],
            'visible' => ['type' => 'boolean', 'required' => true]
        ];
    }
}
