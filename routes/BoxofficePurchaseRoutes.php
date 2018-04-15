<?php

/**
 * 
 */
$app->post('/boxoffice/boxoffice-purchases', Actions\CreateBoxofficePurchaseAction::class);

/**
 * 
 */
$app->get('/boxoffice/boxoffice-purchases/{unique_id}', Actions\GetBoxofficePurchaseAction::class);

/**
 * 
 */
$app->put('/boxoffice/upgrade-order/{id}', Actions\UpgradeOrderToBoxofficePurchaseAction::class);

/**
 * 
 */
$app->get('/boxoffice/tickets/{unique_id}', Actions\GetPdfTicketAction::class);

/**
 * 
 */
$app->get('/admin/boxoffice-purchases', Actions\ListBoxofficePurchasesAction::class);