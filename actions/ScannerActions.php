<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ValidateTicketAction {
    private $orm;
    private $settings;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->settings = $container->get('settings')['Scanner'];
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $key = $args['key'];
        $eventId = $args['eventId'];
        $code = $args['code'];

        $body = $response->getBody();
        if ($key == $this->settings['key']) {
            $scannerResult = null;
            if (in_array($code, [ 'ok', 'error', 'warning' ])) {
                $scannerResult = $this->evaluateTestCode($code, $eventId);
            } else {
                $scannerResult = $this->evaluateRealCode($code, $eventId);
            }
            $body->write(implode('<br>', $scannerResult->messages));
        } else {
            $body->write('Wrong key');
        }
        return $response;
    }

    private function evaluateTestCode($code, $eventId) {
        $eventMapper = $this->orm->mapper('Model\Event');
        $event = $eventMapper->get($eventId);
        if ($event != null) {
            $messages = [
                'Code: ' . $code,
                'Event: ' . $event->name,
                'Date: ' . $event->dateandtime,
                'Location: ' . $event->location
            ];
            
            $scannerStatus = ScannerStatus::Ok;
            if ($code == 'ok') {
                $scannerStatus = ScannerStatus::Ok;
            } else if ($code == 'error') {
                $scannerStatus = ScannerStatus::Error;
            } else if ($code == 'warning') {
                $scannerStatus = ScannerStatus::Warning;
            } else {
                throw new Exception("Unknown test code: " . $code);
            }
            $scannerResult = new ScannerResult($messages, $scannerStatus);
            return $scannerResult;
        } else {
            $messages = [ 'Event not found' ];
            $scannerResult = new ScannerResult($messages, ScannerStatus::Error);
            return $scannerResult;
        }
    }

    private function evaluateRealCode($code, $eventId) {
        $reservationMapper = $this->orm->mapper('Model\Reservation');
        $reservation = $reservationMapper->first([ 'unique_id' => $code, 'event_id' => $eventId ]);
        $scannerResult = null;
        if ($reservation != null) {
            if ($reservation->order_kind == 'boxoffice-purchase' || $reservation->order_kind == 'customer-purchase') {
                if (!$reservation->is_scanned) {
                    // Everything OK!
                    $scannerResult = new ScannerResult([ 'OK' ], ScannerStatus::Ok);

                    // Persist the status
                    $reservation->is_scanned = true;
                    $reservationMapper->update($reservation);
                } else {
                    // Paid, but already seen
                    $scannerResult = new ScannerResult([ 'Already seen' ], ScannerStatus::Warning);
                }
            } else {
                // Reservation, not paid
                $scannerResult = new ScannerResult([ 'Not paid' ], ScannerStatus::Error);
            }
        } else {
            // Reservation not found
            $scannerResult = new ScannerResult([ 'Not found' ], ScannerStatus::Error);
        }
        return $scannerResult;
    }
}

abstract class ScannerStatus {
    const Ok = 0;
    const Warning = 1;
    const Error = 2;
}

class ScannerResult {
    public $messages;
    public $status;
    public function __construct($messages, $status) {
        $this->messages = $messages;
        $this->status = $status;
    }
}