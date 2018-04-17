<?php

require 'vendor/autoload.php';

$app = new \Slim\App(["settings" => [ "displayErrorDetails" => true ]]);

$app->add(new Tuupola\Middleware\CorsMiddleware);

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "secure" => true,
    "relaxed" => ["localhost", "ticketbox-php-test.herokuapp.com", "ticketbox-php.herokuapp.com"],
    "users" => [
        "admin" => "admin"
    ]
]));

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/boxoffice",
    "secure" => true,
    "relaxed" => ["localhost", "ticketbox-php-test.herokuapp.com", "ticketbox-php.herokuapp.com"],
    "users" => [
        "boxoffice" => "boxoffice"
    ]
]));

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/scanner",
    "secure" => true,
    "relaxed" => ["localhost", "ticketbox-php-test.herokuapp.com", "ticketbox-php.herokuapp.com"],
    "users" => [
        "scanner" => "scanner"
    ]
]));

require 'dependencies.php';

require 'routes/EventRoutes.php';
require 'routes/BlockRoutes.php';
require 'routes/SeatRoutes.php';
require 'routes/ReservationRoutes.php';
require 'routes/OrderRoutes.php';
require 'routes/CustomerPurchaseRoutes.php';
require 'routes/BoxofficePurchaseRoutes.php';
require 'routes/UtilityRoutes.php';

$app->run();