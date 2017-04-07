<?php

class EventblockMergerTest extends \PHPUnit_Framework_TestCase {
    private $eventMapperMock;
    private $eventblockMapperMock;
    private $blockMapperMock;
    private $categoryMapperMock;
    private $seatMapperMock;
    private $seatConverterMock;

    private $eventblockMock;

    protected function setUp() {
        $this->eventMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->eventblockMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->blockMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->categoryMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->seatMapperMock = $this->getMockBuilder(\Spot\MapperInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->seatConverterMock = $this->getMockBuilder(Services\SeatConverterInterface::class)
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
    }

    public function testMergeEmptyList() {
        $merger = new Services\EventblockMerger(
            $this->eventMapperMock,
            $this->eventblockMapperMock,
            $this->blockMapperMock,
            $this->categoryMapperMock,
            $this->seatMapperMock,
            $this->seatConverterMock);
        $result = $merger->merge([]);
        $this->assertSame([], $result);
    }

    public function testMergeOneEventblock() {
        $eventblockMock = $this->getEntityMock();
        $eventblockMock
            ->method('get')
            ->willReturn('eid');
        
        $map = [
            ['id', 'bid'],
            ['name', 'bname'],
            ['seatplan_image_data_url', 'url']
        ];
        $blockMock = $this->getEntityMock();
        $blockMock
            ->method('get')
            ->will($this->returnValueMap($map));
        $this->blockMapperMock
            ->method('get')
            ->willReturn($blockMock);
        $merger = new Services\EventblockMerger(
            $this->eventMapperMock,
            $this->eventblockMapperMock,
            $this->blockMapperMock,
            $this->categoryMapperMock,
            $this->seatMapperMock,
            $this->seatConverterMock);

        $result = $merger->merge([ $eventblockMock ]);
        $this->assertSame(1, count($result));
        $this->assertSame('eid', $result[0]->id);
        $this->assertSame('bname', $result[0]->name);
    }

    public function testMergeTwoNotMergableEventblocks() {
        $eventblockPropertiesMap1 = [
            ['id', 'eid1'],
            ['block_id', 'bid1']
        ];
        $eventblockMock1 = $this->getEntityMock();
        $eventblockMock1
            ->method('get')
            ->will($this->returnValueMap($eventblockPropertiesMap1));

        $eventblockPropertiesMap2 = [
            ['id', 'eid2'],
            ['block_id', 'bid2']
        ];
        $eventblockMock2 = $this->getEntityMock();
        $eventblockMock2
            ->method('get')
            ->will($this->returnValueMap($eventblockPropertiesMap2));
        
        $blockPropertiesMap1 = [
            ['id', 'bid1'],
            ['name', 'bname1'],
            ['seatplan_image_data_url', 'url1']
        ];
        $blockMock1 = $this->getEntityMock();
        $blockMock1
            ->method('get')
            ->will($this->returnValueMap($blockPropertiesMap1));

        $blockPropertiesMap2 = [
            ['id', 'bid2'],
            ['name', 'bname2'],
            ['seatplan_image_data_url', 'url2']
        ];
        $blockMock2 = $this->getEntityMock();
        $blockMock2
            ->method('get')
            ->will($this->returnValueMap($blockPropertiesMap2));

        $blocksMap = [
            ['bid1', $blockMock1],
            ['bid2', $blockMock2]
        ];
        $this->blockMapperMock
            ->method('get')
            ->will($this->returnValueMap($blocksMap));
        $merger = new Services\EventblockMerger(
            $this->eventMapperMock,
            $this->eventblockMapperMock,
            $this->blockMapperMock,
            $this->categoryMapperMock,
            $this->seatMapperMock,
            $this->seatConverterMock);

        $result = $merger->merge([ $eventblockMock1, $eventblockMock2 ]);
        $this->assertSame(2, count($result));
        $this->assertSame('eid1', $result[0]->id);
        $this->assertSame('bname1', $result[0]->name);
        $this->assertSame('eid2', $result[1]->id);
        $this->assertSame('bname2', $result[1]->name);
    }

    public function testMergeTwoMergableEventblocks() {
        $eventblockPropertiesMap1 = [
            ['id', 'eid1'],
            ['block_id', 'bid1']
        ];
        $eventblockMock1 = $this->getEntityMock();
        $eventblockMock1
            ->method('get')
            ->will($this->returnValueMap($eventblockPropertiesMap1));

        $eventblockPropertiesMap2 = [
            ['id', 'eid2'],
            ['block_id', 'bid2']
        ];
        $eventblockMock2 = $this->getEntityMock();
        $eventblockMock2
            ->method('get')
            ->will($this->returnValueMap($eventblockPropertiesMap2));
        
        $blockPropertiesMap1 = [
            ['id', 'bid1'],
            ['name', 'bname'],
            ['seatplan_image_data_url', 'url']
        ];
        $blockMock1 = $this->getEntityMock();
        $blockMock1
            ->method('get')
            ->will($this->returnValueMap($blockPropertiesMap1));

        $blockPropertiesMap2 = [
            ['id', 'bid2'],
            ['name', 'bname'],
            ['seatplan_image_data_url', 'url']
        ];
        $blockMock2 = $this->getEntityMock();
        $blockMock2
            ->method('get')
            ->will($this->returnValueMap($blockPropertiesMap2));

        $blocksMap = [
            ['bid1', $blockMock1],
            ['bid2', $blockMock2]
        ];
        $this->blockMapperMock
            ->method('get')
            ->will($this->returnValueMap($blocksMap));
        $merger = new Services\EventblockMerger(
            $this->eventMapperMock,
            $this->eventblockMapperMock,
            $this->blockMapperMock,
            $this->categoryMapperMock,
            $this->seatMapperMock,
            $this->seatConverterMock);

        $result = $merger->merge([ $eventblockMock1, $eventblockMock2 ]);
        $this->assertSame(1, count($result));
        $this->assertSame('eid1-eid2', $result[0]->id);
        $this->assertSame('bname', $result[0]->name);
    }

    public function testGetMergedEventblock() {
        $eventblockPropertiesMap1 = [
            ['id', 'eid1'],
            ['event_id', 'evid1'],
            ['block_id', 'bid1'],
            ['category_id', 'cid1']
        ];
        $eventblockMock1 = $this->getEntityMock();
        $eventblockMock1
            ->method('get')
            ->will($this->returnValueMap($eventblockPropertiesMap1));

        $eventblockPropertiesMap2 = [
            ['id', 'eid2'],
            ['event_id', 'evid2'],
            ['block_id', 'bid2'],
            ['category_id', 'cid2']
        ];
        $eventblockMock2 = $this->getEntityMock();
        $eventblockMock2
            ->method('get')
            ->will($this->returnValueMap($eventblockPropertiesMap2));

        $eventblockMapperMap = [
            ['eid1', $eventblockMock1],
            ['eid2', $eventblockMock2]
        ];
        $this->eventblockMapperMock
            ->method('get')
            ->will($this->returnValueMap($eventblockMapperMap));
        
        $blockPropertiesMap1 = [
            ['id', 'bid1'],
            ['name', 'bname'],
            ['seatplan_image_data_url', 'url']
        ];
        $blockMock1 = $this->getEntityMock();
        $blockMock1
            ->method('get')
            ->will($this->returnValueMap($blockPropertiesMap1));

        $blockPropertiesMap2 = [
            ['id', 'bid2'],
            ['name', 'bname'],
            ['seatplan_image_data_url', 'url']
        ];
        $blockMock2 = $this->getEntityMock();
        $blockMock2
            ->method('get')
            ->will($this->returnValueMap($blockPropertiesMap2));

        $this->eventMapperMock
            ->method('get')
            ->willReturn('event');

        $blockMapperMap = [
            ['bid1', $blockMock1],
            ['bid2', $blockMock2]
        ];
        $this->blockMapperMock
            ->method('get')
            ->will($this->returnValueMap($blockMapperMap));

        $categoryMapperMap = [
            ['cid1', 'category1'],
            ['cid2', 'category2']
        ];
        $this->categoryMapperMock
            ->method('get')
            ->will($this->returnValueMap($categoryMapperMap));

        $this->seatConverterMock
            ->method('convert')
            ->willReturn('seats');

        $merger = new Services\EventblockMerger(
            $this->eventMapperMock,
            $this->eventblockMapperMock,
            $this->blockMapperMock,
            $this->categoryMapperMock,
            $this->seatMapperMock,
            $this->seatConverterMock);

        $mergedEventblock = $merger->getMergedEventblock('eid1-eid2');
        $this->assertSame('eid1-eid2', $mergedEventblock->id);
        $this->assertSame('bname', $mergedEventblock->name);
        $this->assertSame('event', $mergedEventblock->event);
        $this->assertSame('url', $mergedEventblock->seatplan_image_data_url);
        $this->assertSame(2, count($mergedEventblock->parts));
        $this->assertSame('eid1', $mergedEventblock->parts[0]->id);
        $this->assertSame('category1', $mergedEventblock->parts[0]->category);
        $this->assertSame('seats', $mergedEventblock->parts[0]->seats);
        $this->assertSame('eid2', $mergedEventblock->parts[1]->id);
        $this->assertSame('category2', $mergedEventblock->parts[1]->category);
        $this->assertSame('seats', $mergedEventblock->parts[1]->seats);
    }

    private function getEntityMock() {
        $entityMock = $this->getMockBuilder(\Spot\EntityInterface::class)
            ->getMockForAbstractClass();
        return $entityMock;
    }
}