<?php

require '../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$app = new \Slim\App([ 'settings' => $config ]);

require '../dependencies.php';

// Routes
// =============================================================
$app->get('/events', Actions\ListEventsAction::class);

$app->get('/events/{id}', Actions\GetEventAction::class);

$app->post('/events', Actions\CreateEventAction::class);

$app->put('/events/{id}', Actions\CreateEventAction::class);

$app->get('/blocks/{id}', Actions\GetBlockAction::class);

$app->post('/blocks', Actions\CreateBlockAction::class);

$app->put('/blocks/{id}', Actions\CreateBlockAction::class);

$app->get('/reservations', Actions\ListReservationsAction::class);

$app->post('/seats', Actions\CreateSeatsAction::class);

$app->delete('/seats/{id}', Actions\DeleteSeatAction::class);
// =============================================================

$app->run();