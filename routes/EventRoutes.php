<?php

/**
 * @api {get} /events Get all visible events
 * @apiName GetVisibleEvents
 * @apiGroup Event
 * @apiPermission none
 * @apiVersion 0.1.0
 * 
 * @apiSuccess {Number} id Event id
 * @apiSuccess {String} name Event name
 * @apiSuccess {String} location Location name
 * @apiSuccess {String} location_address Location address
 * @apiSuccess {String} location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} location_directions_car Directions for car drivers
 * @apiSuccess {String} dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} visible Visibility of the event
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * [
 *     {
 *         "id": 1,
 *         "name": "Example Event",
 *         "location": "Example Hall",
 *         "location_address": "42 Example Street",
 *         "location_directions_public_transport": "Use the example line",
 *         "location_directions_car": "Turn left, then right.",
 *         "dateandtime": "First sunday in march at 9 am",
 *         "visible": true
 *     }
 * ]
 */
$app->get('/events', Actions\ListVisibleEventsAction::class);

/**
 * @api {get} /events/:id Get one event
 * @apiName GetEvent
 * @apiGroup Event
 * @apiPermission none
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} id Event id
 * 
 * @apiSuccess {Number} id Event id
 * @apiSuccess {String} name Event name
 * @apiSuccess {String} location Location name
 * @apiSuccess {String} location_address Location address
 * @apiSuccess {String} location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} location_directions_car Directions for car drivers
 * @apiSuccess {String} dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} visible Visibility of the event
 * @apiSuccess {Block[]} blocks List of seating blocks
 * @apiSuccess {String} blocks.id Block key
 * @apiSuccess {String} blocks.name Block name
 * @apiSuccess {Boolean} blocks.numbered Is this a numbered block?
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "id": 1,
 *     "name": "Example Event",
 *     "location": "Example Hall",
 *     "location_address": "42 Example Street",
 *     "location_directions_public_transport": "Use the example line",
 *     "location_directions_car": "Turn left, then right.",
 *     "dateandtime": "First sunday in march at 9 am",
 *     "visible": true,
 *     "blocks": [
 *          {
 *              "id": "10",
 *              "name": "Example Block"
 *              "numbered": false
 *          }
 *     ]
 * }
 */
$app->get('/events/{id}', Actions\GetEventWithMergedEventblocksAction::class);

/**
 * @api {get} /admin/events Get all events
 * @apiName GetAllEvents
 * @apiGroup Event
 * @apiPermission admin
 * @apiVersion 0.1.0
 * 
 * @apiSuccess {Number} id Event id
 * @apiSuccess {String} name Event name
 * @apiSuccess {String} location Location name
 * @apiSuccess {String} location_address Location address
 * @apiSuccess {String} location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} location_directions_car Directions for car drivers
 * @apiSuccess {String} dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} visible Visibility of the event
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * [
 *     {
 *         "id": 1,
 *         "name": "Example Event",
 *         "location": "Example Hall",
 *         "location_address": "42 Example Street",
 *         "location_directions_public_transport": "Use the example line",
 *         "location_directions_car": "Turn left, then right.",
 *         "dateandtime": "First sunday in march at 9 am",
 *         "visible": false
 *     }
 * ]
 */
$app->get('/admin/events', Actions\ListAllEventsAction::class);

/**
 * @api {get} /admin/events/:id Get one raw event
 * @apiName GetRawEvent
 * @apiGroup Event
 * @apiPermission admin
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} id Event id
 * 
 * @apiSuccess {Number} id Event id
 * @apiSuccess {String} name Event name
 * @apiSuccess {String} location Location name
 * @apiSuccess {String} location_address Location address
 * @apiSuccess {String} location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} location_directions_car Directions for car drivers
 * @apiSuccess {String} dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} visible Visibility of the event
 * @apiSuccess {Block[]} blocks List of assigned seating blocks
 * @apiSuccess {Number} blocks.id Block id
 * @apiSuccess {Category} blocks.category Block category
 * @apiSuccess {Number} blocks.category.id Category id
 * @apiSuccess {String} blocks.category.name Category name
 * @apiSuccess {String} blocks.category.color Category color
 * @apiSuccess {Number} blocks.category.price Category price
 * @apiSuccess {Number} blocks.category.price_reduced Category price (reduced)
 * @apiSuccess {Block} blocks.block Raw block
 * @apiSuccess {Number} blocks.block.id Raw block id
 * @apiSuccess {Number} blocks.block.seatplan_image_data_url Raw block seatplan as Data URI
 * @apiSuccess {Number} blocks.block.name Raw block name
 * @apiSuccess {Number} blocks.block.numbered Is this a numbered block?
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "id": 22,
 *     "name": "Example Event",
 *     "location": "Example Hall",
 *     "location_address": "42 Example Street",
 *     "location_directions_public_transport": "Use the example line",
 *     "location_directions_car": "Turn left, then right.",
 *     "dateandtime": "First sunday in march at 9 am",
 *     "visible": false,
 *     "blocks": [
 *         {
 *             "id": 32,
 *             "category": {
 *                 "id": 42,
 *                 "name": "Example Category",
 *                 "color": "#000",
 *                 "price": 30,
 *                 "price_reduced": 20
 *             },
 *             "block": {
 *                 "id": 52,
 *                 "seatplan_image_data_url": null,
 *                 "name": "Example block",
 *                 "numbered": true
 *             }
 *         }
 *     ]
 * }
 */
$app->get('/admin/events/{id}', Actions\GetEventAction::class);

/**
 * @api {post} /admin/events Create event
 * @apiName CreateEvent
 * @apiGroup Event
 * @apiPermission admin
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} name Event name
 * @apiParam {String} [location] Location name
 * @apiParam {String} [location_address] Location address
 * @apiParam {String} [location_directions_public_transport] Directions for users of public transport
 * @apiParam {String} [location_directions_car] Directions for car drivers
 * @apiParam {String} [dateandtime] Textual description of the event date and time
 * @apiParam {Boolean} visible Visibility of the event
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "name": "Example Event",
 *   "visible": false
 * }
 * 
 * @apiSuccess (Created 201) {Number} id Event id
 * @apiSuccess (Created 201) {String} name Name
 * @apiSuccess (Created 201) {String} location Location name
 * @apiSuccess (Created 201) {String} location_address Location address
 * @apiSuccess (Created 201) {String} location_directions_public_transport Directions for users of public transport
 * @apiSuccess (Created 201) {String} location_directions_car Directions for car drivers
 * @apiSuccess (Created 201) {String} dateandtime Textual description of the event date and time
 * @apiSuccess (Created 201) {Boolean} visible Visibility of the event
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *     "id": 1,
 *     "name": "Example Event",
 *     "location": "Example Hall",
 *     "location_address": "42 Example Street",
 *     "location_directions_public_transport": "Use the example line",
 *     "location_directions_car": "Turn left, then right.",
 *     "dateandtime": "First sunday in march at 9 am",
 *     "visible": true,
 * }
 */
$app->post('/admin/events', Actions\CreateEventAction::class);

/**
 * @api {put} /admin/events/:id Update event
 * @apiName UpdateEvent
 * @apiGroup Event
 * @apiPermission admin
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} id Event id
 * @apiParam {String} name Event name
 * @apiParam {String} [location] Location name
 * @apiParam {String} [location_address] Location address
 * @apiParam {String} [location_directions_public_transport] Directions for users of public transport
 * @apiParam {String} [location_directions_car] Directions for car drivers
 * @apiParam {String} [dateandtime] Textual description of the event date and time
 * @apiParam {Boolean} visible Visibility of the event
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "name": "Example Event",
 *   "visible": false
 * }
 * 
 * @apiSuccess {Number} id Event id
 * @apiSuccess {String} name Name
 * @apiSuccess {String} location Location name
 * @apiSuccess {String} location_address Location address
 * @apiSuccess {String} location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} location_directions_car Directions for car drivers
 * @apiSuccess {String} dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} visible Visibility of the event
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "id": 1,
 *     "name": "Example Event",
 *     "location": "Example Hall",
 *     "location_address": "42 Example Street",
 *     "location_directions_public_transport": "Use the example line",
 *     "location_directions_car": "Turn left, then right.",
 *     "dateandtime": "First sunday in march at 9 am",
 *     "visible": true,
 * }
 */
$app->put('/admin/events/{id}', Actions\ChangeEventAction::class);

/**
 * @api {delete} /admin/events/:id Delete event
 * @apiName DeleteEvent
 * @apiGroup Event
 * @apiPermission admin
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} id Event id
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 */
$app->delete('/admin/events/{id}', Actions\DeleteEventAction::class);