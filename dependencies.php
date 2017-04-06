<?php

require 'model/Event.php';
require 'model/Block.php';
require 'model/Category.php';
require 'model/Eventblock.php';
require 'model/Order.php';
require 'model/BoxofficePurchase.php';
require 'model/CustomerPurchase.php';
require 'model/Reservation.php';
require 'model/Seat.php';

require 'services/TicketPartWriterInterface.php';
require 'services/ExpandedReservation.php';
require 'services/TicketValidatorResult.php';
require 'services/PathConverter.php';
require 'services/Log.php';
require 'services/EventblockMerger.php';
require 'services/ReservationConverter.php';
require 'services/OrderToBoxofficePurchaseUpgrader.php';
require 'services/TokenProvider.php';
require 'services/SeatReserver.php';
require 'services/SeatConverter.php';
require 'services/MessageFactory.php';
require 'services/Mail.php';
require 'services/Page.php';
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
require 'services/BraintreePaymentProvider.php';
require 'services/TicketValidator.php';

require 'actions/LogActions.php';
require 'actions/EventActions.php';
require 'actions/BlockActions.php';
require 'actions/CategoryActions.php';
require 'actions/EventblockActions.php';
require 'actions/ReservationActions.php';
require 'actions/OrderActions.php';
require 'actions/PurchaseActions.php';
require 'actions/SeatActions.php';
require 'actions/AdminActions.php';
require 'actions/ScannerActions.php';

$container = $app->getContainer();

$container['pathConverter'] = function($container) {
    $root = $container['settings']['root'];
    $converter = new Services\PathConverter($root);
    return $converter;
};

$container['logger'] = function($container) {
    $pathConverter = $container['pathConverter'];
    $logDirectory = $pathConverter->convert($container['settings']['logDirectory']);
    $logger = new Katzgrau\KLogger\Logger($logDirectory);
    return $logger;
};

$container['log'] = function($container) {
    $messageFactory = $container['messageFactory'];
    $mailer = $container['mailer'];
    $logger = $container['logger'];
    $settings = $container['settings']['Log'];
    $log = new Services\Log($messageFactory, $mailer, $logger, $settings);
    return $log;
};

$container['uuidFactory'] = function($container) {
    $uuidFactory = new \Ramsey\Uuid\UuidFactory();
    return $uuidFactory;
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

$container['eventblockMerger'] = function($container) {
    $eventMapper = $container['orm']->mapper('Model\Event');
    $eventblockMapper = $container['orm']->mapper('Model\Eventblock');
    $blockMapper = $container['orm']->mapper('Model\Block');
    $categoryMapper = $container['orm']->mapper('Model\Category');
    $seatMapper = $container['orm']->mapper('Model\Seat');
    $seatConverter = $container['seatConverter'];
    $eventblockMerger = new Services\EventblockMerger($eventMapper, $eventblockMapper, $blockMapper, $categoryMapper, $seatMapper, $seatConverter);
    return $eventblockMerger;
};

$container['reservationConverter'] = function($container) {
    $eventMapper = $container['orm']->mapper('Model\Event');
    $seatMapper = $container['orm']->mapper('Model\Seat');
    $eventblockMapper = $container['orm']->mapper('Model\Eventblock');
    $categoryMapper = $container['orm']->mapper('Model\Category');
    $priceModificators = $container['settings']['PriceModificators'];
    $converter = new Services\ReservationConverter($eventMapper, $seatMapper, $eventblockMapper, $categoryMapper, $priceModificators);
    return $converter;
};

$container['tokenProvider'] = function($container) {
    $provider = new Services\TokenProvider($container['session'], $container['uuidFactory']);
    return $provider;
};

$container['seatReserver'] = function($container) {
    $orderMapper = $container['orm']->mapper('Model\Order');
    $boxofficePurchaseMapper = $container['orm']->mapper('Model\BoxofficePurchase');
    $customerPurchaseMapper = $container['orm']->mapper('Model\CustomerPurchase');
    $reservationMapper = $container['orm']->mapper('Model\Reservation');
    $reservationConverter = $container['reservationConverter'];
    $tokenProvider = $container['tokenProvider'];
    $uuidFactory = $container['uuidFactory'];
    $log = $container['log'];
    $reserver = new Services\SeatReserver(
        $orderMapper,
        $boxofficePurchaseMapper,
        $customerPurchaseMapper,
        $reservationMapper,
        $reservationConverter,
        $tokenProvider,
        $uuidFactory,
        $log,
        $container['settings']['Reservations']);
    return $reserver;
};

$container['orderToBoxofficePurchaseUpgrader'] = function($container) {
    $orderMapper = $container['orm']->mapper('Model\Order');
    $boxofficePurchaseMapper = $container['orm']->mapper('Model\BoxofficePurchase');
    $reservationMapper = $container['orm']->mapper('Model\Reservation');
    $uuidFactory = $container['uuidFactory'];
    $reservationConverter = $container['reservationConverter'];
    $priceModificators = $container['settings']['PriceModificators'];
    $orderToBoxofficePurchaseUpgrader = new Services\OrderToBoxofficePurchaseUpgrader(
        $orderMapper,
        $boxofficePurchaseMapper,
        $reservationMapper,
        $uuidFactory,
        $reservationConverter,
        $priceModificators);
    return $orderToBoxofficePurchaseUpgrader;
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
    $settings = $container['settings']['SeatplanWriter'];
    $seatplanWriter = new Services\SeatplanWriter($blockMapper, $filePersister, $tempDirectory, $settings);
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
    $templateDirectory = $pathConverter->convert($container['settings']['templateDirectory']);
    $tempDirectory = $pathConverter->convert($container['settings']['tempDirectory']);
    $htmlTicketWriter = new Services\HtmlTicketWriter($twig, $templateProvider, $filePersister, $templateDirectory, $tempDirectory);
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

$container['messageFactory'] = function($container) {
    $messageFactory = new Services\MessageFactory();
    return $messageFactory;
};

$container['mail'] = function($container) {
    $twig = $container['twig'];
    $templateProvider = $container['templateProvider'];
    $messageFactory = $container['messageFactory'];
    $mailer = $container['mailer'];
    $pdfTicketWriter = $container['pdfTicketWriter'];
    $log = $container['log'];
    $settings = $container['settings']['Mailer'];
    $mail = new Services\Mail($twig, $templateProvider, $messageFactory, $mailer, $pdfTicketWriter, $log, $settings);
    return $mail;
};

$container['paymentProvider'] = function($container) {
    if ($container['settings']['PaymentProvider']['gateway'] == 'Braintree') {
        $settings = $container['settings']['PaymentProvider']['settings'];
        $log = $container['log'];
        $paymentProvider = new Services\BraintreePaymentProvider($log, $settings);
        return $paymentProvider;
    } else {
        throw new \Exception('Unknown payment gateway.');
    }
};

$container['page'] = function($container) {
    $twig = $container['twig'];
    $templateProvider = $container['templateProvider'];
    $page = new Services\Page($twig, $templateProvider);
    return $page;
};

$container['ticketValidator'] = function($container) {
    $reservationMapper = $container['orm']->mapper('Model\Reservation');
    $log = $container['log'];
    $secretKey = $container['settings']['Scanner']['secretKey'];
    $ticketValidator = new Services\TicketValidator($reservationMapper, $log, $secretKey);
    return $ticketValidator;
};

$container['ticketTestValidator'] = function($container) {
    $eventMapper = $container['orm']->mapper('Model\Event');
    $secretKey = $container['settings']['Scanner']['secretKey'];
    $ticketTestValidator = new Services\TicketTestValidator($eventMapper, $secretKey);
    return $ticketTestValidator;
};