<?php

require 'model/Event.php';
require 'model/Block.php';
require 'model/Category.php';
require 'model/Eventblock.php';
require 'model/Order.php';
require 'model/BoxofficePurchase.php';
require 'model/Reservation.php';
require 'model/Seat.php';

require 'services/ReservationConverter.php';
require 'services/TokenProvider.php';
require 'services/SeatReserver.php';
require 'services/SeatConverter.php';
require 'services/Mail.php';

require 'actions/EventActions.php';
require 'actions/BlockActions.php';
require 'actions/CategoryActions.php';
require 'actions/EventblockActions.php';
require 'actions/ReservationActions.php';
require 'actions/OrderActions.php';
require 'actions/PurchaseActions.php';

$container = $app->getContainer();

$container['orm'] = function($container) {
    $spotConfig = new \Spot\Config();
    $spotConfig->addConnection('sqlite', $container['settings']['Spot']);
    $spot = new \Spot\Locator($spotConfig);
    return $spot;
};

$container['session'] = function($container) {
    $session = new duncan3dc\Sessions\SessionInstance('token');
    return $session;
};

$container['reservationConverter'] = function($container) {
    $eventMapper = $container['orm']->mapper('Model\Event');
    $seatMapper = $container['orm']->mapper('Model\Seat');
    $eventblockMapper = $container['orm']->mapper('Model\Eventblock');
    $categoryMapper = $container['orm']->mapper('Model\Category');
    $converter = new Services\ReservationConverter($eventMapper, $seatMapper, $eventblockMapper, $categoryMapper);
    return $converter;
};

$container['tokenProvider'] = function($container) {
    $provider = new Services\TokenProvider($container['session']);
    return $provider;
};

$container['seatReserver'] = function($container) {
    $orderMapper = $container['orm']->mapper('Model\Order');
    $boxofficePurchaseMapper = $container['orm']->mapper('Model\BoxofficePurchase');
    $reservationMapper = $container['orm']->mapper('Model\Reservation');
    $reservationConverter = $container['reservationConverter'];
    $tokenProvider = $container['tokenProvider'];
    $reserver = new Services\SeatReserver(
        $orderMapper,
        $boxofficePurchaseMapper,
        $reservationMapper,
        $reservationConverter,
        $tokenProvider,
        $container['settings']['Reservations']);
    return $reserver;
};

$container['seatConverter'] = function($container) {
    $reservationMapper = $container['orm']->mapper('Model\Reservation');
    $tokenProvider = $container['tokenProvider'];
    $converter = new Services\SeatConverter($reservationMapper, $tokenProvider, $container['settings']['Reservations']);
    return $converter;
};

$container['template'] = function($container) {
    $template = new \Latte\Engine;
    return $template;
};

$container['mailer'] = function($container) {
    $mailer = new \Nette\Mail\SendmailMailer;
    return $mailer;
};

$container['mail'] = function($container) {
    $mail = new Services\Mail($container['template'], $container['mailer'], $container['settings']['Mailer']);
    return $mail;
};