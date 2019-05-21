<?php

require '../../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$config['root'] = __DIR__;
$app = new \Slim\App([ 'settings' => $config ]);

require '../../core/dependencies.php';

// Routes
// =============================================================
$app->get('/boxoffice-purchases/{boxoffice_name}', Actions\ListThinBoxofficePurchasesAction::class);
$app->put('/boxoffice-purchases/{id}', Actions\MarkBoxofficePurchasePrintStatusAction::class);
$app->get('/tickets/{unique_id}', Actions\GetPdfTicketAction::class);
// =============================================================

$app->run();