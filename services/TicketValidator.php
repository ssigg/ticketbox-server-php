<?php

namespace Services;

interface TicketValidatorInterface {
    function validate($key, $eventId, $code);
}

class TicketValidator implements TicketValidatorInterface {
    private $reservationMapper;
    private $secretKey;

    public function __construct(\Spot\MapperInterface $reservationMapper, $secretKey) {
        $this->reservationMapper = $reservationMapper;
        $this->secretKey = $secretKey;
    }

    public function validate($key, $eventId, $code) {
        $ticketValidatorResult = null;
        if ($key == $this->secretKey) {
            $reservation = $this->reservationMapper->first([ 'unique_id' => $code, 'event_id' => $eventId ]);
            if ($reservation != null) {
                if ($reservation->get('order_kind') == 'boxoffice-purchase' || $reservation->get('order_kind') == 'customer-purchase') {
                    if (!$reservation->get('is_scanned')) {
                        // Everything OK!
                        $ticketValidatorResult = new TicketValidatorResult([ 'OK' ], TicketValidatorStatus::Ok);

                        // Persist the status
                        $reservation->is_scanned = true;
                        $this->reservationMapper->update($reservation);
                    } else {
                        // Paid, but already seen
                        $ticketValidatorResult = new TicketValidatorResult([ 'Already seen' ], TicketValidatorStatus::Warning);
                    }
                } else {
                    // Reservation, not paid
                    $ticketValidatorResult = new TicketValidatorResult([ 'Not paid' ], TicketValidatorStatus::Error);
                }
            } else {
                // Reservation not found
                $ticketValidatorResult = new TicketValidatorResult([ 'Not found' ], TicketValidatorStatus::Error);
            }
        } else {
            $ticketValidatorResult = new TicketValidatorResult([ 'Wrong key' ], TicketValidatorStatus::Error);
        }
        return $ticketValidatorResult;
    }
}

class TicketTestValidator implements TicketValidatorInterface {
    private $eventMapper;
    private $secretKey;

    public function __construct(\Spot\MapperInterface $eventMapper, $secretKey) {
        $this->eventMapper = $eventMapper;
        $this->secretKey = $secretKey;
    }

    public function validate($key, $eventId, $code) {
        $ticketValidatorResult = null;
        if ($key == $this->secretKey) {
            $event = $this->eventMapper->get($eventId);
            if ($event != null) {
                $messages = [
                    'Code: ' . $code,
                    'Event: ' . $event->get('name'),
                    'Date: ' . $event->get('dateandtime'),
                    'Location: ' . $event->get('location')
                ];

                if ($code == 'ok') {
                    $ticketValidatorResult = new TicketValidatorResult($messages, TicketValidatorStatus::Ok);
                } else if ($code == 'error') {
                    $ticketValidatorResult = new TicketValidatorResult($messages, TicketValidatorStatus::Error);
                } else if ($code == 'warning') {
                    $ticketValidatorResult = new TicketValidatorResult($messages, TicketValidatorStatus::Warning);
                } else {
                    $ticketValidatorResult = new TicketValidatorResult([ 'Unknown code ' . $code ], TicketValidatorStatus::Error);
                }
            } else {
                $ticketValidatorResult = new TicketValidatorResult([ 'Event not found' ], TicketValidatorStatus::Error);
            }
        } else {
            $ticketValidatorResult = new TicketValidatorResult([ 'Wrong key' ], TicketValidatorStatus::Error);
        }
        return $ticketValidatorResult;
    }
}