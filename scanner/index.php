<?php

require '../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$config['root'] = __DIR__;
$app = new \Slim\App([ 'settings' => $config ]);

require '../dependencies.php';

// Routes
// =============================================================
$app->get('/enable/{eventId}', Actions\EnableDeviceAction::class);
$app->get('/disable/{eventId}', Actions\DisableDeviceAction::class);
$app->get('/validate/{code}', Actions\ValidateTicketAction::class);
// =============================================================

$app->run();