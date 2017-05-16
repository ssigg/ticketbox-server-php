<?php

namespace Services;

interface EventblockMergerInterface {
    function merge($eventblocks);
    function getMergedEventblock($key);
}

class EventblockMerger implements EventblockMergerInterface {
    private $eventMapper;
    private $eventblockMapper;
    private $blockMapper;
    private $categoryMapper;
    private $seatMapper;
    private $seatConverter;
    
    public function __construct(\Spot\MapperInterface $eventMapper, \Spot\MapperInterface $eventblockMapper, \Spot\MapperInterface $blockMapper, \Spot\MapperInterface $categoryMapper, \Spot\MapperInterface $seatMapper, SeatConverterInterface $seatConverter) {
        $this->eventMapper = $eventMapper;
        $this->eventblockMapper = $eventblockMapper;
        $this->blockMapper = $blockMapper;
        $this->categoryMapper = $categoryMapper;
        $this->seatMapper = $seatMapper;
        $this->seatConverter = $seatConverter;
    }

    public function merge($eventblocks) {
        $thinMergedEventblocks = [];
        foreach ($eventblocks as $eventblock) {
            $block = $this->blockMapper->get($eventblock->get('block_id'));
            if (!$this->tryMerge($eventblock, $block, $thinMergedEventblocks)) {
                $thinMergedEventblock = ThinMergedEventblock::createNew($eventblock, $block);
                $thinMergedEventblocks[] = $thinMergedEventblock;
            }
        }
        return $thinMergedEventblocks;
    }

    public function getMergedEventblock($key) {
        $eventblockIds = explode('-', $key);
        $eventblock = $this->eventblockMapper->get($eventblockIds[0]);

        $event = $this->eventMapper->get($eventblock->get('event_id'));
        $block = $this->blockMapper->get($eventblock->get('block_id'));

        $mergedEventblockParts = [];
        foreach ($eventblockIds as $eventblockId) {
            $eventblock = $this->eventblockMapper->get($eventblockId);
            $category = $this->categoryMapper->get($eventblock->get('category_id'));
            $seats = $this->seatMapper->where([ 'block_id' => $eventblock->get('block_id') ]);
            $convertedSeats = $this->seatConverter->convert($seats, $eventblock);
            $mergedEventblockPart = new MergedEventblockPart($eventblockId, $category, $convertedSeats);
            $mergedEventblockParts[] = $mergedEventblockPart;
        }
        
        $mergedEventblock = new MergedEventblock(MergedEventblock::encodeId($eventblockIds), $block->get('name'), $event, $block->get('seatplan_image_data_url'), $mergedEventblockParts);
        return $mergedEventblock;
    }

    private function tryMerge($eventblock, $block, $thinMergedEventblocks) {
        foreach ($thinMergedEventblocks as $thinMergedEventblock) {
            if ($thinMergedEventblock->tryMerge($eventblock, $block)) {
                return true;
            }
        }
        return false;
    }
}

class ThinMergedEventblock {
    public $id;
    public $name;
    
    private $seatplan_image_data_url;
    private $eventblockIds;

    public static function createNew($eventBlock, $block) {
        $id = self::encodeId([ $eventBlock->get('id') ]);
        return new ThinMergedEventblock($id, $block->get('name'), $block->get('seatplan_image_data_url'));
    }

    private static function encodeId($eventblockIds) {
        $idParts = [];
        foreach ($eventblockIds as $eventblockId) {
            $idParts[] = $eventblockId;
        }
        return implode('-', $idParts);
    }

    private function __construct($id, $name, $seatplan_image_data_url) {
        $this->id = $id;
        $this->name = $name;
        $this->seatplan_image_data_url = $seatplan_image_data_url;
        $this->eventblockIds = [ $id ];
    }

    public function tryMerge($eventblock, $block) {
        if ($this->name == $block->get('name') && $this->seatplan_image_data_url == $block->get('seatplan_image_data_url')) {
            $this->eventblockIds[] = $eventblock->get('id');
            $this->id = self::encodeId($this->eventblockIds);
            return true;
        } else {
            return false;
        }
    }
}

class MergedEventblock {
    public $id;
    public $name;
    public $event;
    public $seatplan_image_data_url;
    public $parts;

    public static function encodeId($eventblockIds) {
        $idParts = [];
        foreach ($eventblockIds as $eventblockId) {
            $idParts[] = $eventblockId;
        }
        return implode('-', $idParts);
    }

    public function __construct($id, $name, $event, $seatplan_image_data_url, $parts) {
        $this->id = $id;
        $this->name = $name;
        $this->event = $event;
        $this->seatplan_image_data_url = $seatplan_image_data_url;
        $this->parts = $parts;
    }
}

class MergedEventblockPart {
    public $id;
    public $category;
    public $seats;

    public function __construct($id, $category, $seats) {
        $this->id = $id;
        $this->category = $category;
        $this->seats = $seats;
    }
}