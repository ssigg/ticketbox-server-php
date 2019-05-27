<?php

namespace Model;

class Event extends \Spot\Entity {
    protected static $table = 'events';
    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'name' => ['type' => 'string', 'required' => true],
            'location' => ['type' => 'string', 'required' => false],
            'location_address' => ['type' => 'string', 'required' => false],
            'location_directions_public_transport' => ['type' => 'string', 'required' => false],
            'location_directions_car' => ['type' => 'string', 'required' => false],
            'dateandtime' => ['type' => 'string', 'required' => false],
            'visible' => ['type' => 'boolean', 'required' => true],
            'logo_image_data_url' => ['type' => 'text', 'required' => false]
        ];
    }
}
