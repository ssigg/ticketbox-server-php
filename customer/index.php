<?php

require '../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$config['root'] = __DIR__;
$app = new \Slim\App([ 'settings' => $config ]);

require '../dependencies.php';

// Routes
// =============================================================
$app->get('/events', Actions\ListVisibleEventsAction::class);
$app->get('/events/{id}', Actions\GetEventAction::class);

$app->get('/eventblocks/{key}', Actions\GetMergedEventblockAction::class);

$app->get('/reservations', Actions\ListMyReservationsAction::class);
$app->post('/reservations', Actions\CreateReservationAction::class);
$app->put('/reservations/{id}', Actions\ChangeReductionForReservationAction::class);
$app->delete('/reservations/{id}', Actions\DeleteReservationAction::class);

$app->get('/reservations-expiration-timestamp', Actions\GetReservationsExpirationTimestampAction::class);

$app->post('/orders', Actions\CreateOrderAction::class);
$app->get('/orders/{unique_id}', Actions\GetOrderAction::class);

$app->get('/customer-purchase-token', Actions\GetCustomerPurchaseTokenAction::class);
$app->post('/customer-purchases', Actions\CreateCustomerPurchaseAction::class);
$app->get('/customer-purchases/{unique_id}', Actions\GetCustomerPurchaseAction::class);

$app->post('/log', Actions\LogClientMessageAction::class);
// =============================================================

$app->run();