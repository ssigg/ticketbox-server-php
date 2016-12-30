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
require 'services/PathConverter.php';
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
require 'services/PdfRendererBinary.php';
require 'services/PdfRendererFactory.php';
require 'services/HtmlToPdfTicketConverter.php';
require 'services/TicketPartTempFilesRemover.php';
require 'services/PdfTicketWriter.php';

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

$container['pathConverter'] = function($container) {
    $root = $container['settings']['root'];
    $converter = new Services\PathConverter($root);
    return $converter;
};

$container['orm'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $spotSettings = $container['settings']['Spot'];
    $spotSettings['path'] = $pathConverter->convert($spotSettings['path']);

    $spotConfig = new \Spot\Config();
    $spotConfig->addConnection('sqlite', $spotSettings);
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
    $pathConverter = $container['pathConverter'];
    $qrWriter = $container['qrWriter'];
    $tempDirectory = $pathConverter->convert($container['settings']['tempDirectory']);
    $qrCodeWriter = new Services\QrCodeWriter($qrWriter, $tempDirectory);
    return $qrCodeWriter;
};

$container['seatplanWriter'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $blockMapper = $container['orm']->mapper('Model\Block');
    $filePersister = $container['filePersister'];
    $tempDirectory = $pathConverter->convert($container['settings']['tempDirectory']);
    $seatplanWriter = new Services\SeatplanWriter($blockMapper, $filePersister, $tempDirectory);
    return $seatplanWriter;
};

$container['twig'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $templateDirectoryPath = $pathConverter->convert($container['settings']['templateDirectory']);
    $loader = new \Twig_Loader_Filesystem($templateDirectoryPath);
    $twig = new \Twig_Environment($loader, [ 'cache' => false ]);
    return $twig;
};

$container['templateProvider'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $filePersister = $container['filePersister'];
    $templateDirectory = $pathConverter->convert($container['settings']['templateDirectory']);
    $templateProvider = new Services\TemplateProvider($filePersister, $templateDirectory);
    return $templateProvider;
};

$container['htmlTicketWriter'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $twig = $container['twig'];
    $templateProvider = $container['templateProvider'];
    $filePersister = $container['filePersister'];
    $tempDirectory = $pathConverter->convert($container['settings']['tempDirectory']);
    $htmlTicketWriter = new Services\HtmlTicketWriter($twig, $templateProvider, $filePersister, $tempDirectory);
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

$container['pdfRendererFactory'] = function($container) {
    $pdfRendererBinary = $container['pdfRendererBinary'];
    $pdfRendererFactory = new Services\PdfRendererFactory($pdfRendererBinary);
    return $pdfRendererFactory;
};

$container['htmlToPdfTicketConverter'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $pdfRendererFactory = $container['pdfRendererFactory'];
    $outputDirectory = $pathConverter->convert($container['settings']['ticketDirectory']);
    $htmlToPdfTicketConverter = new Services\HtmlToPdfTicketConverter($pdfRendererFactory, $outputDirectory);
    return $htmlToPdfTicketConverter;
};

$container['ticketPartTempFilesRemover'] = function($container) {
    $filePersister = $container['filePersister'];
    $ticketPartTempFilesRemover = new Services\TicketPartTempFilesRemover($filePersister);
    return $ticketPartTempFilesRemover;
};

$container['pdfTicketWriter'] = function($container) {
    $qrCodeWriter = $container['qrCodeWriter'];
    $seatplanWriter = $container['seatplanWriter'];
    $htmlTicketWriter = $container['htmlTicketWriter'];
    $htmlToPdfTicketConverter = $container['htmlToPdfTicketConverter'];
    $ticketPartTempFilesRemover = $container['ticketPartTempFilesRemover'];
    $ticketPartWriters = [
        $qrCodeWriter,
        $seatplanWriter,
        $htmlTicketWriter,
        $htmlToPdfTicketConverter,
        $ticketPartTempFilesRemover
    ];
    $pdfTicketWriter = new Services\PdfTicketWriter($ticketPartWriters);
    return $pdfTicketWriter;
};

$container['mailer'] = function($container) {
    $mailer = new \Nette\Mail\SendmailMailer;
    return $mailer;
};

$container['mail'] = function($container) {
    $twig = $container['twig'];
    $templateProvider = $container['templateProvider'];
    $mailer = $container['mailer'];
    $pdfTicketWriter = $container['pdfTicketWriter'];
    $settings = $container['settings']['Mailer'];
    $mail = new Services\Mail($twig, $templateProvider, $mailer, $pdfTicketWriter, $settings);
    return $mail;
};