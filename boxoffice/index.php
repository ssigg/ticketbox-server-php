<?php

require '../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$config['root'] = __DIR__;
$app = new \Slim\App([ 'settings' => $config ]);

require '../dependencies.php';

// Routes
// =============================================================
$app->get('/events', Actions\ListVisibleEventsAction::class);
$app->get('/events/{id}', Actions\GetEventWithMergedEventblocksAction::class);

$app->get('/eventblocks/{key}', Actions\GetMergedEventblockAction::class);

$app->get('/reservations', Actions\ListMyReservationsAction::class);
$app->post('/reservations', Actions\CreateReservationAction::class);
$app->post('/unspecified-reservations', Actions\CreateUnspecifiedReservationsAction::class);
$app->put('/reservations/{id}', Actions\ChangeReductionForReservationAction::class);
$app->delete('/reservations/{id}', Actions\DeleteReservationAction::class);

$app->get('/reservations-expiration-timestamp', Actions\GetReservationsExpirationTimestampAction::class);

$app->get('/orders', Actions\ListOrdersAction::class);

$app->post('/boxoffice-purchases', Actions\CreateBoxofficePurchaseAction::class);
$app->get('/boxoffice-purchases/{unique_id}', Actions\GetBoxofficePurchaseAction::class);
$app->put('/upgrade-order/{id}', Actions\UpgradeOrderToBoxofficePurchaseAction::class);

$app->get('/tickets/{unique_id}', Actions\GetPdfTicketAction::class);

$app->post('/log', Actions\LogClientMessageAction::class);
// =============================================================

$app->run();