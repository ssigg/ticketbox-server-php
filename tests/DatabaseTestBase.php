<?php

require dirname(__FILE__) . '/../model/Event.php';
require dirname(__FILE__) . '/../model/Block.php';
require dirname(__FILE__) . '/../model/Category.php';
require dirname(__FILE__) . '/../model/Eventblock.php';
require dirname(__FILE__) . '/../model/Order.php';
require dirname(__FILE__) . '/../model/BoxofficePurchase.php';
require dirname(__FILE__) . '/../model/Reservation.php';
require dirname(__FILE__) . '/../model/Seat.php';

abstract class DatabaseTestBase extends \PHPUnit_Framework_TestCase {
    protected $container;

    protected function setUp() {
        $this->container = $this->createContainer();
    }

    protected function tearDown() {
        unlink(dirname(__FILE__) . '/data/database.db');
    }

    protected function getGetRequest($path) {
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => $path]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        return $request;
    }

    protected function getPostRequest($path, $data) {
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => $path]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody($data);
        return $request;
    }

    protected function getPutRequest($path, $data) {
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI' => $path]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody($data);
        return $request;
    }

    protected function getDeleteRequest($path) {
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
            'REQUEST_URI' => $path]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        return $request;
    }

    private function createContainer() {
        $spot = $this->configureSpot();
        $container = new \Slim\Container;
        $container['orm'] = $spot;
        return $container;
    }

    private function configureSpot() {
        $testDbConnection = [
            'dbsyntax' => 'sqlite',
            'path' => 'tests/data/database.db',
            'driver' => 'pdo_sqlite'
        ];
        $spotConfig = new \Spot\Config();
        $spotConfig->addConnection('sqlite', $testDbConnection);
        $spot = new \Spot\Locator($spotConfig);

        $mappers = [
            $spot->mapper('Model\Event'),
            $spot->mapper('Model\Block'),
            $spot->mapper('Model\Category'),
            $spot->mapper('Model\Eventblock'),
            $spot->mapper('Model\Order'),
            $spot->mapper('Model\BoxofficePurchase'),
            $spot->mapper('Model\Reservation'),
            $spot->mapper('Model\Seat'),
        ];
        foreach($mappers as $mapper) {
            $mapper->migrate();
        }

        $eventMapper = $spot->mapper('Model\Event');
        $eventMapper->create([
            'name' => 'Event 1',
            'location' => 'Location 1',
            'dateandtime' => 'Date and Time 1' ]);

        $eventblockMapper = $spot->mapper('Model\Eventblock');
        $eventblockMapper->create([
            'event_id' => 1,
            'block_id' => 1,
            'category_id' => 1 ]);

        $blockMapper = $spot->mapper('Model\Block');
        $blockMapper->create([
            'name' => 'Block 1',
            'seatplan_image_data_url' => 'data_url' ]);

        $categoryMapper = $spot->mapper('Model\Category');
        $categoryMapper->create([
            'name' => 'Category 1',
            'price' => 2,
            'price_reduced' => 1 ]);

        $seatMapper = $spot->mapper('Model\Seat');
        $seatMapper->create([
            'block_id' => 1,
            'name' => 'Seat 1',
            'x0' => 0.0,
            'y0' => 1.0,
            'x1' => 2.0,
            'y1' => 3.0,
            'x2' => 4.0,
            'y2' => 5.0,
            'x3' => 6.0,
            'y3' => 7.0 ]);

        $orderMapper = $spot->mapper('Model\Order');
        $orderMapper->create([
            'title' => 'm',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@example.com',
            'locale' => 'en',
            'timestamp' => 123
        ]);

        $boxofficePurchaseMapper = $spot->mapper('Model\BoxofficePurchase');
        $boxofficePurchaseMapper->create([
            'boxoffice' => 'Box Office',
            'locale' => 'en',
            'timestamp' => 123
        ]);

        $reservationMapper = $spot->mapper('Model\Reservation');
        $reservationMapper->create([
            'token' => 'abc',
            'seat_id' => 1,
            'event_id' => 1,
            'category_id' => 1,
            'is_reduced' => false,
            'timestamp' => time()]);

        return $spot;
    }
}