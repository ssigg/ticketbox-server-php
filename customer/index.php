<?php

require '../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);

// Create app
$app = new \Slim\App([ 'settings' => $config ]);

$container = $app->getContainer();

require 'dependencies.php';

// Define app routes
$app->get('/events', Actions\ListEventsAction::class);

$app->get('/events/{id}', Actions\GetEventAction::class);

$app->get('/blocks/{id}', Actions\GetBlockAction::class);

$app->post('/reservations', Actions\CreateReservationAction::class);

$app->delete('/reservations/{seatId}', Actions\DeleteReservationAction::class);

$app->put('/reservations/addReduction/{seatId}', Actions\AddReductionToReservationAction::class);

$app->put('/reservations/removeReduction/{seatId}', Actions\RemovReductionFromReservationAction::class);

$app->post('/orders', Actions\CreateOrderAction::class);

// Run app
$app->run();