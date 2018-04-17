<?php

require 'vendor/autoload.php';

$app = new \Slim\App();

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/admin",
    "users" => [
        "admin" => "admin"
    ]
]));

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/boxoffice",
    "users" => [
        "boxoffice" => "boxoffice"
    ]
]));

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/scanner",
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