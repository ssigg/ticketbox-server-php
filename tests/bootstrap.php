<?php
// Set timezone
date_default_timezone_set('America/New_York');

// Prevent session cookies
ini_set('session.use_cookies', 0);

// Enable Composer autoloader
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// Register actions
require dirname(__DIR__) . '/actions/EventActions.php';
require dirname(__DIR__) . '/actions/BlockActions.php';
require dirname(__DIR__) . '/actions/OrderActions.php';

// Register services
require dirname(__DIR__) . '/services/TokenProvider.php';
require dirname(__DIR__) . '/services/SeatReserver.php';
require dirname(__DIR__) . '/services/SeatConverter.php';
require dirname(__DIR__) . '/services/Mail.php';

require dirname(__FILE__) . '/DatabaseTestBase.php';

// Register test classes
$autoloader->addPsr4('tests\\', __DIR__);