<?php

require '../vendor/autoload.php';

$config = json_decode(file_get_contents("config/config.json"), true);
$config['root'] = __DIR__;
$app = new \Slim\App([ 'settings' => $config ]);

require '../dependencies.php';

// Routes
// =============================================================

/**
 * @api {get} /events Get all visible events
 * @apiName GetEvents
 * @apiGroup Event
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
 * @api {get} /eventsblocks/:key Get one block
 * @apiName GetBlocks
 * @apiGroup Event
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} key Block key
 * 
 * @apiSuccess {String} id Block id
 * @apiSuccess {String} name Block name
 * @apiSuccess {String} numbered Is this a numbered block?
 * @apiSuccess {Event} event The event
 * @apiSuccess {Number} event.id Event id
 * @apiSuccess {String} event.location Location name
 * @apiSuccess {String} event.location_address Location address
 * @apiSuccess {String} event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} event.location_directions_car Directions for car drivers
 * @apiSuccess {String} event.dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} event.visible Visibility of the event
 * @apiSuccess {String} seatplan_image_data_url A Data URI-formatted image of a seat plan
 * @apiSuccess {Part[]} parts Parts of this block
 * @apiSuccess {Number} parts.id Part id
 * @apiSuccess {Category} parts.category Category
 * @apiSuccess {Number} parts.category.id Category id
 * @apiSuccess {String} parts.category.name Category name
 * @apiSuccess {String} parts.category.color Category color
 * @apiSuccess {String} parts.category.price Category price
 * @apiSuccess {String} parts.category.price_reduced Category price (reduced)
 * @apiSuccess {Seat[]} parts.seats Seats of this part
 * @apiSuccess {Seat} parts.seats.seat Seat
 * @apiSuccess {Number} parts.seats.seat.id Seat id
 * @apiSuccess {Number} parts.seats.seat.block_id Seats block id
 * @apiSuccess {String} parts.seats.seat.name Seat name
 * @apiSuccess {Number} parts.seats.seat.x0 Coordinate
 * @apiSuccess {Number} parts.seats.seat.y0 Coordinate
 * @apiSuccess {Number} parts.seats.seat.x1 Coordinate
 * @apiSuccess {Number} parts.seats.seat.y1 Coordinate
 * @apiSuccess {Number} parts.seats.seat.x2 Coordinate
 * @apiSuccess {Number} parts.seats.seat.y2 Coordinate
 * @apiSuccess {Number} parts.seats.seat.x3 Coordinate
 * @apiSuccess {Number} parts.seats.seat.y3 Coordinate
 * @apiSuccess {String} parts.seats.state Seat state (free|reservedbymyself|reserved|sold)
 * @apiSuccess {Number} parts.seats.reservation_id Reservation id
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "id": "10",
 *   "name": "Block One",
 *   "numbered": false,
 *   "event": {
 *       "id": 1,
 *       "name": "Example Event",
 *       "location": "Example Hall",
 *       "location_address": "42 Example Street",
 *       "location_directions_public_transport": "Use the example line",
 *       "location_directions_car": "Turn left, then right.",
 *       "dateandtime": "First sunday in march at 9 am",
 *       "visible": true,
 *   },
 *   "seatplan_image_data_url": "none",
 *   "parts": [
 *       {
 *           "id": "10",
 *           "category": {
 *               "id": 3,
 *               "name": "Block One",
 *               "color": "#000",
 *               "price": 30,
 *               "price_reduced": 20
 *           },
 *           "seats": [
 *               {
 *                   "seat": {
 *                       "id": 77,
 *                       "block_id": 22,
 *                       "name": "Seat One",
 *                       "x0": null,
 *                       "y0": null,
 *                       "x1": null,
 *                       "y1": null,
 *                       "x2": null,
 *                       "y2": null,
 *                       "x3": null,
 *                       "y3": null
 *                   },
 *                   "state": "sold",
 *                   "reservation_id": null
 *               }
 *           ]
 *       }
 *   ]
 * }
 */
$app->get('/eventblocks/{key}', Actions\GetMergedEventblockAction::class);

/**
 * @api {get} /reservations List user reservations
 * @apiName ListMyReservations
 * @apiGroup Reservation
 * @apiVersion 0.1.0
 * 
 * @apiSuccess {Number} id Reservation id
 * @apiSuccess {String} unique_id Reservation's unique id
 * @apiSuccess {Event} event Reservation event
 * @apiSuccess {Number} event.id Event id
 * @apiSuccess {String} event.location Location name
 * @apiSuccess {String} event.location_address Location address
 * @apiSuccess {String} event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} event.location_directions_car Directions for car drivers
 * @apiSuccess {String} event.dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} event.visible Visibility of the event
 * @apiSuccess {Seat} seat Reservation seat
 * @apiSuccess {Number} seat.id Seat id
 * @apiSuccess {Number} seat.block_id Seats block id
 * @apiSuccess {String} seat.name Seat name
 * @apiSuccess {Number} seat.x0 Coordinate
 * @apiSuccess {Number} seat.y0 Coordinate
 * @apiSuccess {Number} seat.x1 Coordinate
 * @apiSuccess {Number} seat.y1 Coordinate
 * @apiSuccess {Number} seat.x2 Coordinate
 * @apiSuccess {Number} seat.y2 Coordinate
 * @apiSuccess {Number} seat.x3 Coordinate
 * @apiSuccess {Number} seat.y3 Coordinate
 * @apiSuccess {Category} category Reservation category
 * @apiSuccess {Number} category.id Category id
 * @apiSuccess {String} category.name Category name
 * @apiSuccess {String} category.color Category color
 * @apiSuccess {String} category.price Category price
 * @apiSuccess {String} category.price_reduced Category price (reduced)
 * @apiSuccess {Boolean} isReduced Is this reservation reduced?
 * @apiSuccess {Number} price Reservation's price
 * @apiSuccess {Number} order_id Reservation order id (null if not ordered yet)
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * [
 *   {
 *     "id": 3869,
 *     "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *     "event": {
 *       "id": 1,
 *       "name": "Example Event",
 *       "location": "Example Hall",
 *       "location_address": "42 Example Street",
 *       "location_directions_public_transport": "Use the example line",
 *       "location_directions_car": "Turn left, then right.",
 *       "dateandtime": "First sunday in march at 9 am",
 *       "visible": true,
 *     },
 *     "seat": {
 *        "id": 77,
 *        "block_id": 22,
 *        "name": "Seat One",
 *        "x0": null,
 *        "y0": null,
 *        "x1": null,
 *        "y1": null,
 *        "x2": null,
 *        "y2": null,
 *        "x3": null,
 *        "y3": null
 *     },
 *     "category": {
 *       "id": 3,
 *       "name": "Block One",
 *       "color": "#000",
 *       "price": 30,
 *       "price_reduced": 20
 *     },
 *     "isReduced": false,
 *     "price": 30,
 *     "order_id": null
 *   }
 * ]
 */
$app->get('/reservations', Actions\ListMyReservationsAction::class);

/**
 * @api {post} /reservations Reserve a specific seat
 * @apiName ReserveSeat
 * @apiGroup Reservation
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} event_id: Event id
 * @apiParam {Number} seat_id: Seat id
 * @apiParam {Number} category_id: Category id
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "event_id": 1,
 *   "seat_id": 22,
 *   "category_id": 77
 * }
 * 
 * @apiSuccess (Created 201) {Number} id Reservation id
 * @apiSuccess (Created 201) {String} unique_id Reservation's unique id
 * @apiSuccess (Created 201) {Event} event Reservation event
 * @apiSuccess (Created 201) {Number} event.id Event id
 * @apiSuccess (Created 201) {String} event.location Location name
 * @apiSuccess (Created 201) {String} event.location_address Location address
 * @apiSuccess (Created 201) {String} event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess (Created 201) {String} event.location_directions_car Directions for car drivers
 * @apiSuccess (Created 201) {String} event.dateandtime Textual description of the event date and time
 * @apiSuccess (Created 201) {Boolean} event.visible Visibility of the event
 * @apiSuccess (Created 201) {Seat} seat Reservation seat
 * @apiSuccess (Created 201) {Number} seat.id Seat id
 * @apiSuccess (Created 201) {Number} seat.block_id Seats block id
 * @apiSuccess (Created 201) {String} seat.name Seat name
 * @apiSuccess (Created 201) {Number} seat.x0 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y0 Coordinate
 * @apiSuccess (Created 201) {Number} seat.x1 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y1 Coordinate
 * @apiSuccess (Created 201) {Number} seat.x2 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y2 Coordinate
 * @apiSuccess (Created 201) {Number} seat.x3 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y3 Coordinate
 * @apiSuccess (Created 201) {Category} category Reservation category
 * @apiSuccess (Created 201) {Number} category.id Category id
 * @apiSuccess (Created 201) {String} category.name Category name
 * @apiSuccess (Created 201) {String} category.color Category color
 * @apiSuccess (Created 201) {String} category.price Category price
 * @apiSuccess (Created 201) {String} category.price_reduced Category price (reduced)
 * @apiSuccess (Created 201) {Boolean} isReduced Is this reservation reduced?
 * @apiSuccess (Created 201) {Number} price Reservation's price
 * @apiSuccess (Created 201) {Number} order_id Reservation order id (null if not ordered yet)
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "id": 3869,
 *   "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *   "event": {
 *     "id": 1,
 *     "name": "Example Event",
 *     "location": "Example Hall",
 *     "location_address": "42 Example Street",
 *     "location_directions_public_transport": "Use the example line",
 *     "location_directions_car": "Turn left, then right.",
 *     "dateandtime": "First sunday in march at 9 am",
 *     "visible": true,
 *   },
 *   "seat": {
 *      "id": 77,
 *      "block_id": 22,
 *      "name": "Seat One",
 *      "x0": null,
 *      "y0": null,
 *      "x1": null,
 *      "y1": null,
 *      "x2": null,
 *      "y2": null,
 *      "x3": null,
 *      "y3": null
 *   },
 *   "category": {
 *     "id": 3,
 *     "name": "Block One",
 *     "color": "#000",
 *     "price": 30,
 *     "price_reduced": 20
 *   },
 *   "isReduced": false,
 *   "price": 30,
 *   "order_id": null
 * }
 * 
 * @apiError (SeatAlreadyReserved) 409 This seat cannot be reserved because a different user has it reserved already.
 * 
 * @apiErrorExample {json} Error-Response:
 * HTTP/1.1 409 Conflict
 */
$app->post('/reservations', Actions\CreateReservationAction::class);

/**
 * @api {post} /unspecified-reservations Reserve amount of unspecified seats
 * @apiName ReserveUnspecifiedSeats
 * @apiGroup Reservation
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} eventblock_id: Block id
 * @apiParam {Number} number_of_seats: Number of seats to be reserved
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "eventblock_id": 2,
 *   "number_of_seats": 42
 * }
 * 
 * @apiSuccess (Created 201) {Number} id Reservation id
 * @apiSuccess (Created 201) {String} unique_id Reservation's unique id
 * @apiSuccess (Created 201) {Event} event Reservation event
 * @apiSuccess (Created 201) {Number} event.id Event id
 * @apiSuccess (Created 201) {String} event.location Location name
 * @apiSuccess (Created 201) {String} event.location_address Location address
 * @apiSuccess (Created 201) {String} event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess (Created 201) {String} event.location_directions_car Directions for car drivers
 * @apiSuccess (Created 201) {String} event.dateandtime Textual description of the event date and time
 * @apiSuccess (Created 201) {Boolean} event.visible Visibility of the event
 * @apiSuccess (Created 201) {Seat} seat Reservation seat
 * @apiSuccess (Created 201) {Number} seat.id Seat id
 * @apiSuccess (Created 201) {Number} seat.block_id Seats block id
 * @apiSuccess (Created 201) {String} seat.name Seat name
 * @apiSuccess (Created 201) {Number} seat.x0 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y0 Coordinate
 * @apiSuccess (Created 201) {Number} seat.x1 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y1 Coordinate
 * @apiSuccess (Created 201) {Number} seat.x2 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y2 Coordinate
 * @apiSuccess (Created 201) {Number} seat.x3 Coordinate
 * @apiSuccess (Created 201) {Number} seat.y3 Coordinate
 * @apiSuccess (Created 201) {Category} category Reservation category
 * @apiSuccess (Created 201) {Number} category.id Category id
 * @apiSuccess (Created 201) {String} category.name Category name
 * @apiSuccess (Created 201) {String} category.color Category color
 * @apiSuccess (Created 201) {String} category.price Category price
 * @apiSuccess (Created 201) {String} category.price_reduced Category price (reduced)
 * @apiSuccess (Created 201) {Boolean} isReduced Is this reservation reduced?
 * @apiSuccess (Created 201) {Number} price Reservation's price
 * @apiSuccess (Created 201) {Number} order_id Reservation order id (null if not ordered yet)
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * [
 *   {
 *     "id": 3869,
 *     "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *     "event": {
 *       "id": 1,
 *       "name": "Example Event",
 *       "location": "Example Hall",
 *       "location_address": "42 Example Street",
 *       "location_directions_public_transport": "Use the example line",
 *       "location_directions_car": "Turn left, then right.",
 *       "dateandtime": "First sunday in march at 9 am",
 *       "visible": true,
 *     },
 *     "seat": {
 *        "id": 77,
 *        "block_id": 22,
 *        "name": "Seat One",
 *        "x0": null,
 *        "y0": null,
 *        "x1": null,
 *        "y1": null,
 *        "x2": null,
 *        "y2": null,
 *        "x3": null,
 *        "y3": null
 *     },
 *     "category": {
 *       "id": 3,
 *       "name": "Block One",
 *       "color": "#000",
 *       "price": 30,
 *       "price_reduced": 20
 *     },
 *     "isReduced": false,
 *     "price": 30,
 *     "order_id": null
 *   }
 * ]
 */
$app->post('/unspecified-reservations', Actions\CreateUnspecifiedReservationsAction::class);

/**
 * @api {put} /reservations Change reduction
 * @apiName ChangeReduction
 * @apiGroup Reservation
 * @apiVersion 0.1.0
 * 
 * @apiParam {Boolean} isReduced: New value of the reduction property
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "isReduced": true
 * }
 * 
 * @apiSuccess {Number} id Reservation id
 * @apiSuccess {String} unique_id Reservation's unique id
 * @apiSuccess {Event} event Reservation event
 * @apiSuccess {Number} event.id
 * @apiSuccess {String} event.location Location name
 * @apiSuccess {String} event.location_address Location address
 * @apiSuccess {String} event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} event.location_directions_car Directions for car drivers
 * @apiSuccess {String} event.dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} event.visible Visibility of the event
 * @apiSuccess {Seat} seat Reservation seat
 * @apiSuccess {Number} seat.id Seat id
 * @apiSuccess {Number} seat.block_id Seats block id
 * @apiSuccess {String} seat.name Seat name
 * @apiSuccess {Number} seat.x0 Coordinate
 * @apiSuccess {Number} seat.y0 Coordinate
 * @apiSuccess {Number} seat.x1 Coordinate
 * @apiSuccess {Number} seat.y1 Coordinate
 * @apiSuccess {Number} seat.x2 Coordinate
 * @apiSuccess {Number} seat.y2 Coordinate
 * @apiSuccess {Number} seat.x3 Coordinate
 * @apiSuccess {Number} seat.y3 Coordinate
 * @apiSuccess {Category} category Reservation category
 * @apiSuccess {Number} category.id Category id
 * @apiSuccess {String} category.name Category name
 * @apiSuccess {String} category.color Category color
 * @apiSuccess {String} category.price Category price
 * @apiSuccess {String} category.price_reduced Category price (reduced)
 * @apiSuccess {Boolean} isReduced Is this reservation reduced?
 * @apiSuccess {Number} price Reservation's price
 * @apiSuccess {Number} order_id Reservation order id (null if not ordered yet)
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "id": 3869,
 *   "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *   "event": {
 *     "id": 1,
 *     "name": "Example Event",
 *     "location": "Example Hall",
 *     "location_address": "42 Example Street",
 *     "location_directions_public_transport": "Use the example line",
 *     "location_directions_car": "Turn left, then right.",
 *     "dateandtime": "First sunday in march at 9 am",
 *     "visible": true,
 *   },
 *   "seat": {
 *      "id": 77,
 *      "block_id": 22,
 *      "name": "Seat One",
 *      "x0": null,
 *      "y0": null,
 *      "x1": null,
 *      "y1": null,
 *      "x2": null,
 *      "y2": null,
 *      "x3": null,
 *      "y3": null
 *   },
 *   "category": {
 *     "id": 3,
 *     "name": "Block One",
 *     "color": "#000",
 *     "price": 30,
 *     "price_reduced": 20
 *   },
 *   "isReduced": true,
 *   "price": 30,
 *   "order_id": null
 * }
 */
$app->put('/reservations/{id}', Actions\ChangeReductionForReservationAction::class);

/**
 * @api {delete} /reservations/:id Delete reservation
 * @apiName DeleteReservation
 * @apiGroup Reservation
 * @apiVersion 0.1.0
 * 
 * @apiParam {Number} id Reservation id
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 */
$app->delete('/reservations/{id}', Actions\DeleteReservationAction::class);

/**
 * @api {get} /reservations-expiration-timestamp Get reservation expiration timestamp
 * @apiName GetReservationExpirationTimestamp
 * @apiGroup Reservation
 * @apiVersion 0.1.0
 * 
 * @apiSuccess {Number} value Unix timestamp when reservations will expire
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "value": 1521293290
 * }
 */
$app->get('/reservations-expiration-timestamp', Actions\GetReservationsExpirationTimestampAction::class);

/**
 * @api {post} /orders Create an order
 * @apiName CreateOrder
 * @apiDescription Currently reserved seats will be added to this order. The currently reserved seats can be obtained using GET /reservations.
 * @apiGroup Order
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} title Customer title (m|f)
 * @apiParam {String} firstname Cutomer firstname
 * @apiParam {String} lastname Customer lastname
 * @apiParam {String} email Customer email
 * @apiParam {String} locale Customer locale
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "title": "m",
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john.doe@example.com",
 *   "locale": "en"
 * } 
 * 
 * @apiSuccess (Created 201) {Number} id Order id
 * @apiSuccess (Created 201) {String} unique_id Unique order id
 * @apiSuccess (Created 201) {String} title Customer title (m|f)
 * @apiSuccess (Created 201) {String} firstname Customer firstname
 * @apiSuccess (Created 201) {String} lastname Customer lastname
 * @apiSuccess (Created 201) {String} email Customer email
 * @apiSuccess (Created 201) {String} locale Customer locale
 * @apiSuccess (Created 201) {Number} timestamp Order timestamp
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "id": 22,
 *   "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *   "title": "m",
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john.doe@example.com",
 *   "locale": "en",
 *   "timestamp": 1521293290
 * }
 */
$app->post('/orders', Actions\CreateOrderAction::class);

/**
 * @api {get} /orders/:unique_id Get an order
 * @apiName GetOrder
 * @apiGroup Order
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} unique_id Unique order id
 * 
 * @apiSuccess {Number} id Order id
 * @apiSuccess {String} unique_id Unique order id
 * @apiSuccess {String} title Customer title (m|f)
 * @apiSuccess {String} firstname Customer firstname
 * @apiSuccess {String} lastname Customer lastname
 * @apiSuccess {String} email Customer email
 * @apiSuccess {String} locale Customer locale
 * @apiSuccess {Number} timestamp Order timestamp
 * @apiSuccess {Number} totalPrice: Total price
 * @apiSuccess {Reservation[]} reservations Order reservations
 * @apiSuccess {Number} reservations.id Reservation id
 * @apiSuccess {String} reservations.unique_id Reservation unique id
 * @apiSuccess {Boolean} reservations.isReduced Is this a reduced seat?
 * @apiSuccess {Number} reservations.order_id Order id
 * @apiSuccess {Number} reservations.price Actual price (reduction already taken into account if any)
 * @apiSuccess {Category} reservations.category Category
 * @apiSuccess {Number} reservations.category.id Category id
 * @apiSuccess {String} reservations.category.name Category name
 * @apiSuccess {String} reservations.category.color Category color
 * @apiSuccess {String} reservations.category.price Category price
 * @apiSuccess {String} reservations.category.price_reduced Category price (reduced)
 * @apiSuccess {Event} reservations.event Event
 * @apiSuccess {Number} reservations.event.id
 * @apiSuccess {String} reservations.event.location Location name
 * @apiSuccess {String} reservations.event.location_address Location address
 * @apiSuccess {String} reservations.event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} reservations.event.location_directions_car Directions for car drivers
 * @apiSuccess {String} reservations.event.dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} reservations.event.visible Visibility of the event
 * @apiSuccess {Seat} reservations.seat Reserved seat
 * @apiSuccess {Number} reservations.seat.id Seat id
 * @apiSuccess {Number} reservations.seat.block_id Seat block id
 * @apiSuccess {String} reservations.seat.name Seat name
 * @apiSuccess {Number} reservations.seat.x0 Coordinate
 * @apiSuccess {Number} reservations.seat.y0 Coordinate
 * @apiSuccess {Number} reservations.seat.x1 Coordinate
 * @apiSuccess {Number} reservations.seat.y1 Coordinate
 * @apiSuccess {Number} reservations.seat.x2 Coordinate
 * @apiSuccess {Number} reservations.seat.y2 Coordinate
 * @apiSuccess {Number} reservations.seat.x3 Coordinate
 * @apiSuccess {Number} reservations.seat.y3 Coordinate
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "id": 22,
 *   "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *   "title": "m",
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john.doe@example.com",
 *   "locale": "en",
 *   "timestamp": 1521293290
 *   "reservations": [
 *     {
 *       "id": 3869,
 *       "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *       "event": {
 *         "id": 1,
 *         "name": "Example Event",
 *         "location": "Example Hall",
 *         "location_address": "42 Example Street",
 *         "location_directions_public_transport": "Use the example line",
 *         "location_directions_car": "Turn left, then right.",
 *         "dateandtime": "First sunday in march at 9 am",
 *         "visible": true,
 *       },
 *       "seat": {
 *          "id": 77,
 *          "block_id": 22,
 *          "name": "Seat One",
 *          "x0": null,
 *          "y0": null,
 *          "x1": null,
 *          "y1": null,
 *          "x2": null,
 *          "y2": null,
 *          "x3": null,
 *          "y3": null
 *       },
 *       "category": {
 *         "id": 3,
 *         "name": "Block One",
 *         "color": "#000",
 *         "price": 30,
 *         "price_reduced": 20
 *       },
 *       "isReduced": true,
 *       "price": 30,
 *       "order_id": null
 *     }
 *   ]
 * }
 */
$app->get('/orders/{unique_id}', Actions\GetOrderAction::class);

/**
 * @api {get} /customer-purchase-token Get a Braintree purchase token
 * @apiName GetCustomerPurchaseToken
 * @apiGroup CustomerPurchase
 * @apiVersion 0.1.0
 * 
 * @apiSuccess {String} value Token value
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "value":"tokenValue"
 * }
 */
$app->get('/customer-purchase-token', Actions\GetCustomerPurchaseTokenAction::class);

/**
 * @api {post} /customer-purchases Create a customer purchase
 * @apiName CreateCustomerPurchase
 * @apiDescription Currently reserved seats will be added to this purchase. The currently reserved seats can be obtained using GET /reservations.
 * @apiGroup CustomerPurchase
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} nonce Nonce obtained from Braintree API
 * @apiParam {String} title Customer title (m|f)
 * @apiParam {String} firstname Cutomer firstname
 * @apiParam {String} lastname Customer lastname
 * @apiParam {String} email Customer email
 * @apiParam {String} locale Customer locale
 * 
 * @apiParamExample {json} Request-Example:
 * {
 *   "nonce": "nonceValue",
 *   "title": "m",
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john.doe@example.com",
 *   "locale": "en"
 * } 
 * 
 * @apiSuccess (Created 201) {Number} id Customer purchase id
 * @apiSuccess (Created 201) {String} unique_id Unique customer purchase id
 * @apiSuccess (Created 201) {String} title Customer title (m|f)
 * @apiSuccess (Created 201) {String} firstname Customer firstname
 * @apiSuccess (Created 201) {String} lastname Customer lastname
 * @apiSuccess (Created 201) {String} email Customer email
 * @apiSuccess (Created 201) {String} locale Customer locale
 * @apiSuccess (Created 201) {Number} timestamp Order timestamp
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 201 Created
 * {
 *   "id": 22,
 *   "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *   "title": "m",
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john.doe@example.com",
 *   "locale": "en",
 *   "timestamp": 1521293290
 * }
 */
$app->post('/customer-purchases', Actions\CreateCustomerPurchaseAction::class);

/**
 * @api {get} /customer-purchases/:unique_id Get a customer purchase
 * @apiName GetCustomerPurchase
 * @apiGroup CustomerPurchase
 * @apiVersion 0.1.0
 * 
 * @apiParam {String} unique_id Unique customer purchase id
 * 
 * @apiSuccess {Number} id Customer purchase id
 * @apiSuccess {String} unique_id Unique customer purchase id
 * @apiSuccess {String} title Customer title (m|f)
 * @apiSuccess {String} firstname Customer firstname
 * @apiSuccess {String} lastname Customer lastname
 * @apiSuccess {String} email Customer email
 * @apiSuccess {String} locale Customer locale
 * @apiSuccess {Number} timestamp Purchase timestamp
 * @apiSuccess {Number} totalPrice: Total price
 * @apiSuccess {Reservation[]} reservations Order reservations
 * @apiSuccess {Number} reservations.id Reservation id
 * @apiSuccess {String} reservations.unique_id Reservation unique id
 * @apiSuccess {Boolean} reservations.isReduced Is this a reduced seat?
 * @apiSuccess {Number} reservations.order_id Order id
 * @apiSuccess {Number} reservations.price Actual price (reduction already taken into account if any)
 * @apiSuccess {Category} reservations.category Category
 * @apiSuccess {Number} reservations.category.id Category id
 * @apiSuccess {String} reservations.category.name Category name
 * @apiSuccess {String} reservations.category.color Category color
 * @apiSuccess {String} reservations.category.price Category price
 * @apiSuccess {String} reservations.category.price_reduced Category price (reduced)
 * @apiSuccess {Event} reservations.event Event
 * @apiSuccess {Number} reservations.event.id
 * @apiSuccess {String} reservations.event.location Location name
 * @apiSuccess {String} reservations.event.location_address Location address
 * @apiSuccess {String} reservations.event.location_directions_public_transport Directions for users of public transport
 * @apiSuccess {String} reservations.event.location_directions_car Directions for car drivers
 * @apiSuccess {String} reservations.event.dateandtime Textual description of the event date and time
 * @apiSuccess {Boolean} reservations.event.visible Visibility of the event
 * @apiSuccess {Seat} reservations.seat Reserved seat
 * @apiSuccess {Number} reservations.seat.id Seat id
 * @apiSuccess {Number} reservations.seat.block_id Seat block id
 * @apiSuccess {String} reservations.seat.name Seat name
 * @apiSuccess {Number} reservations.seat.x0 Coordinate
 * @apiSuccess {Number} reservations.seat.y0 Coordinate
 * @apiSuccess {Number} reservations.seat.x1 Coordinate
 * @apiSuccess {Number} reservations.seat.y1 Coordinate
 * @apiSuccess {Number} reservations.seat.x2 Coordinate
 * @apiSuccess {Number} reservations.seat.y2 Coordinate
 * @apiSuccess {Number} reservations.seat.x3 Coordinate
 * @apiSuccess {Number} reservations.seat.y3 Coordinate
 * 
 * @apiSuccessExample {json} Success-Response:
 * HTTP/1.1 200 OK
 * {
 *   "id": 22,
 *   "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *   "title": "m",
 *   "firstname": "John",
 *   "lastname": "Doe",
 *   "email": "john.doe@example.com",
 *   "locale": "en",
 *   "timestamp": 1521293290
 *   "reservations": [
 *     {
 *       "id": 3869,
 *       "unique_id": "3e51b64c-29e1-11e8-b4f8-002590daa0f6",
 *       "event": {
 *         "id": 1,
 *         "name": "Example Event",
 *         "location": "Example Hall",
 *         "location_address": "42 Example Street",
 *         "location_directions_public_transport": "Use the example line",
 *         "location_directions_car": "Turn left, then right.",
 *         "dateandtime": "First sunday in march at 9 am",
 *         "visible": true,
 *       },
 *       "seat": {
 *          "id": 77,
 *          "block_id": 22,
 *          "name": "Seat One",
 *          "x0": null,
 *          "y0": null,
 *          "x1": null,
 *          "y1": null,
 *          "x2": null,
 *          "y2": null,
 *          "x3": null,
 *          "y3": null
 *       },
 *       "category": {
 *         "id": 3,
 *         "name": "Block One",
 *         "color": "#000",
 *         "price": 30,
 *         "price_reduced": 20
 *       },
 *       "isReduced": true,
 *       "price": 30,
 *       "order_id": null
 *     }
 *   ]
 * }
 */
$app->get('/customer-purchases/{unique_id}', Actions\GetCustomerPurchaseAction::class);

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
// =============================================================

$app->run();