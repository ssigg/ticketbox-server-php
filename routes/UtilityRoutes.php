<?php

/**
 * @api {post} /log Send a log message
 * @apiName SendLogMessage
 * @apiDescription Currently reserved seats will be added to this order. The currently reserved seats can be obtained using GET /reservations.
 * @apiGroup Log
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} severity (info|warning|error)
 * @apiParam {String} message Log message
 * @apiParam {String} userData User data like stack traces
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "severity": "info",
 *   "message": "Sold two tickets",
 *   "userData": ""
 * } 
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 */
$app->post('/log', Actions\LogClientMessageAction::class);

/**
 * 
 */
$app->post('/admin/migrate', Actions\MigrateAction::class);

/**
 * 
 */
$app->get('/scanner/validate/{key}/{eventId}/{code}', Actions\ValidateTicketAction::class);