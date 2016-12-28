<?php

require 'model/Event.php';
require 'model/Block.php';
require 'model/Category.php';
require 'model/Eventblock.php';
require 'model/Order.php';
require 'model/BoxofficePurchase.php';
require 'model/Reservation.php';
require 'model/Seat.php';

require 'services/TicketPartWriterInterface.php';
require 'services/ExpandedReservation.php';
require 'services/ReservationConverter.php';
require 'services/TokenProvider.php';
require 'services/SeatReserver.php';
require 'services/SeatConverter.php';
require 'services/Mail.php';
require 'services/FilePersister.php';
require 'services/QrCodeWriter.php';
require 'services/SeatplanWriter.php';
require 'services/TemplateProvider.php';
require 'services/HtmlTicketWriter.php';

require 'actions/EventActions.php';
require 'actions/BlockActions.php';
require 'actions/CategoryActions.php';
require 'actions/EventblockActions.php';
require 'actions/ReservationActions.php';
require 'actions/OrderActions.php';
require 'actions/PurchaseActions.php';
require 'actions/SeatActions.php';
require 'actions/AdminActions.php';

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

$container['filePersister'] = function($container) {
    $filePersister = new Services\FilePersister();
    return $filePersister;
};

$container['qrWriter'] = function($container) {
    $renderer = new \BaconQrCode\Renderer\Image\Png();
    $renderer->setWidth(256);
    $renderer->setHeight(256);
    $renderer->setMargin(0);
    $writer = new \BaconQrCode\Writer($renderer);
    return $writer;
};

$container['qrCodeWriter'] = function($container) {
    $qrCodeWriter = new Services\QrCodeWriter($container['qrWriter'], $container['settings']['Mailer']['tempDirectory']);
    return $qrCodeWriter;
};

$container['seatplanWriter'] = function($container) {
    $blockMapper = $container['orm']->mapper('Model\Block');
    $outputDirectoryPath = $container['settings']['tempDirectory'];
    $seatplanWriter = new Services\SeatplanWriter($blockMapper, $outputDirectoryPath);
    return $seatplanWriter;
};

$container['twig'] = function($container) {
    $templateDirectoryPath = $container['settings']['templateDirectory'];
    $loader = new \Twig_Loader_Filesystem($templateDirectoryPath);
    $twig = new \Twig_Environment($loader, [ 'cache' => false ]);
    return $twig;
};

$container['htmlTicketWriter'] = function($container) {
    $twig = $container['twig'];
    $templateProvider = $container['templateProvider'];
    $filePersister = $container['filePersister'];
    $outputDirectoryPath = $container['settings']['tempDirectory'];
    $htmlTicketWriter = new Services\HtmlTicketWriter($twig, $templateProvider, $filePersister, $outputDirectoryPath);
    return $htmlTicketWriter;
};

$container['operatingSystem'] = function($container) {
    $operatingSystem = new \Tivie\OS\Detector();
    return $operatingSystem;
};

$container['pdfRendererBinary'] = function($container) {
    $pdfRendererBinary = new Services\PdfRendererBinary($container['operatingSystem']);
    return $pdfRendererBinary;
};

$container['pdfRenderer'] = function($container) {
    $options = [
        'binary' => $container['pdfRendererBinary']->getPath()
    ];
    $pdfRenderer = new \mikehaertl\wkhtmlto\Pdf($options);
    return $pdfRenderer;
};

$container['htmlToPdfTicketConverter'] = function($container) {
    $pdfRenderer = $container['pdfRenderer'];
    $outputDirectoryPath = $container['settings']['tempDirectory'];
    $htmlToPdfTicketConverter = new Services\HtmlToPdfTicketConverter($pdfRenderer, $outputDirectoryPath);
    return $htmlToPdfTicketConverter;
};

$container['pdfTicketWriter'] = function($container) {
    $qrCodeWriter = $container['qrCodeWriter'];
    $seatplanWriter = $container['seatplanWriter'];
    $htmlTicketWriter = $container['htmlTicketWriter'];
    $htmlToPdfTicketConverter = $container['htmlToPdfTicketConverter'];
    $ticketPartWriters = [
        $qrCodeWriter,
        $seatplanWriter,
        $htmlTicketWriter,
        $htmlToPdfTicketConverter
    ];
    $pdfTicketWriter = new Services\PdfTicketWriter($ticketPartWriters);
    return $pdfTicketWriter;
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
    $mail = new Services\Mail($container['template'], $container['mailer'], $container['pdfTicketWriter'], $container['settings']['Mailer']);
    return $mail;
};