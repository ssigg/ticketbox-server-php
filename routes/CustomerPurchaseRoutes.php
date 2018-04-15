<?php

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
 * 
 */
$app->get('/admin/customer-purchases', Actions\ListCustomerPurchasesAction::class);