<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class EnableDeviceAction {
    private $orm;
    private $session;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->session = $container->get('session');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $eventId = $args['eventId'];
        $mapper = $this->orm->mapper('Model\Event');
        $event = $mapper->get($eventId);
        if ($event != null) {
            $this->session->set('enabledForEvent', $eventId);
        }
        $body = $response->getBody();
        $body->write('<p>Enabled for event ' . $event->name . '</p>');
        return $response;
    }
}

class DisableDeviceAction {
    private $orm;
    private $session;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->session = $container->get('session');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $eventId = $args['eventId'];
        $mapper = $this->orm->mapper('Model\Event');
        $event = $mapper->get($eventId);
        if ($event != null) {
            $this->session->delete('enabledForEvent');
        }
        $body = $response->getBody();
        $body->write('<p>Disabled for event ' . $event->name . '</p>');
        return $response;
    }
}

class ValidateTicketAction {
    private $orm;
    private $session;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->session = $container->get('session');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $eventId = $this->session->get('enabledForEvent');
        $body = $response->getBody();
        
        if ($eventId != null) {
            $code = $args['code'];
            $reservationMapper = $this->orm->mapper('Model\Reservation');
            $reservation = $reservationMapper->first([ 'unique_id' => $code, 'event_id' => $eventId ]);
            if ($reservation != null) {
                if ($reservation->order_kind == 'boxoffice-purchase' || $reservation->order_kind == 'customer-purchase') {
                    if (!$reservation->is_scanned) {
                        // Everything OK!
                        $body->write('<body style="background:#3d3;font-family:Georgia, serif;font-size:10em;text-align:center;">OK</body>');

                        // Persist the status
                        $reservation->is_scanned = true;
                        $reservationMapper->update($reservation);
                    } else {
                        // Paid, but already seen
                        $body->write('<body style="background:#dd3;font-family:Georgia, serif;font-size:10em;text-align:center;">Already seen</body>');
                    }
                } else {
                    // Reservation, not paid
                    $body->write('<body style="background:#d33;font-family:Georgia, serif;font-size:10em;text-align:center;">Not paid</body>');
                }
            } else {
                // Reservation not found
                $body->write('<body style="background:#d33;font-family:Georgia, serif;font-size:10em;text-align:center;">Not found</body>');
            }
        } else {
            // Do something for visitors
            $body->write('You have scanned the ticket.');
        }
        
        return $response;
    }
}