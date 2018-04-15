<?php

/**
 * 
 */
$app->get('/admin/seats', Actions\ListSeatsAction::class);

/**
 * 
 */
$app->post('/admin/seats', Actions\CreateSeatsAction::class);

/**
 * 
 */
$app->delete('/admin/seats/{id}', Actions\DeleteSeatAction::class);