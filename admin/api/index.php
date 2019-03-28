<?php

require '../../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$config['root'] = __DIR__;
$app = new \Slim\App([ 'settings' => $config ]);

require '../../core/dependencies.php';

// Routes
// =============================================================
$app->get('/events', Actions\ListAllEventsAction::class);
$app->get('/events/{id}', Actions\GetEventAction::class);
$app->post('/events', Actions\CreateEventAction::class);
$app->put('/events/{id}', Actions\ChangeEventAction::class);
$app->delete('/events/{id}', Actions\DeleteEventAction::class);

$app->get('/eventblocks', Actions\ListEventblocksAction::class);
$app->get('/eventblocks/{id}', Actions\GetEventblockAction::class);
$app->post('/eventblocks', Actions\CreateEventblockAction::class);
$app->put('/eventblocks/{id}', Actions\ChangeEventblockAction::class);
$app->delete('/eventblocks/{id}', Actions\DeleteEventblockAction::class);

$app->get('/blocks', Actions\ListBlocksAction::class);
$app->get('/blocks/{id}', Actions\GetBlockAction::class);
$app->post('/blocks', Actions\CreateBlockAction::class);
$app->put('/blocks/{id}', Actions\ChangeBlockAction::class);
$app->delete('/blocks/{id}', Actions\DeleteBlockAction::class);

$app->get('/categories', Actions\ListCategoriesAction::class);
$app->post('/categories', Actions\CreateCategoryAction::class);
$app->put('/categories/{id}', Actions\ChangeCategoryAction::class);
$app->delete('/categories/{id}', Actions\DeleteCategoryAction::class);

$app->get('/reservations', Actions\ListAllReservationsAction::class);

$app->get('/seats', Actions\ListSeatsAction::class);
$app->post('/seats', Actions\CreateSeatsAction::class);
$app->delete('/seats/{id}', Actions\DeleteSeatAction::class);

$app->get('/orders', Actions\ListOrdersAction::class);

$app->get('/boxoffice-purchases', Actions\ListBoxofficePurchasesAction::class);

$app->get('/customer-purchases', Actions\ListCustomerPurchasesAction::class);

$app->post('/migrate', Actions\MigrateAction::class);
// =============================================================

$app->run();