<?php
// Set timezone
date_default_timezone_set('America/New_York');

// Prevent session cookies
ini_set('session.use_cookies', 0);

// Enable Composer autoloader
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// Register actions
require dirname(__DIR__) . '/actions/EventActions.php';
require dirname(__DIR__) . '/actions/EventblockActions.php';
require dirname(__DIR__) . '/actions/BlockActions.php';
require dirname(__DIR__) . '/actions/CategoryActions.php';
require dirname(__DIR__) . '/actions/SeatActions.php';
require dirname(__DIR__) . '/actions/ReservationActions.php';
require dirname(__DIR__) . '/actions/OrderActions.php';
require dirname(__DIR__) . '/actions/PurchaseActions.php';

// Register services
require dirname(__DIR__) . '/services/TicketPartWriterInterface.php';
require dirname(__DIR__) . '/services/ExpandedReservation.php';
require dirname(__DIR__) . '/services/PathConverter.php';
require dirname(__DIR__) . '/services/ReservationConverter.php';
require dirname(__DIR__) . '/services/OrderToBoxofficePurchaseUpgrader.php';
require dirname(__DIR__) . '/services/TokenProvider.php';
require dirname(__DIR__) . '/services/SeatReserver.php';
require dirname(__DIR__) . '/services/SeatConverter.php';
require dirname(__DIR__) . '/services/MessageFactory.php';
require dirname(__DIR__) . '/services/Mail.php';
require dirname(__DIR__) . '/services/FilePersister.php';
require dirname(__DIR__) . '/services/QrCodeWriter.php';
require dirname(__DIR__) . '/services/SeatplanWriter.php';
require dirname(__DIR__) . '/services/TemplateProvider.php';
require dirname(__DIR__) . '/services/HtmlTicketWriter.php';
require dirname(__DIR__) . '/services/PdfRendererBinary.php';
require dirname(__DIR__) . '/services/PdfRendererFactory.php';
require dirname(__DIR__) . '/services/HtmlToPdfTicketConverter.php';
require dirname(__DIR__) . '/services/TicketPartTempFilesRemover.php';
require dirname(__DIR__) . '/services/PdfTicketWriter.php';

require dirname(__FILE__) . '/DatabaseTestBase.php';

// Register test classes
$autoloader->addPsr4('tests\\', __DIR__);