<?php

namespace Services;

interface SeatReserverInterface {
    function getReservations();
    function reserve($seats, $event, $category);
    function release($reservationId);
    function getReservationsExpirationTimestamp();
    function changeReduction($reservationId, $value);
    function order($title, $firstname, $lastname, $email, $locale);
    function boxofficePurchase($boxofficeName, $locale);
    function customerPurchase($title, $firstname, $lastname, $email, $locale);
    function getTotalPriceOfPendingReservations();
}

class SeatReserver implements SeatReserverInterface {
    private $orderMapper;
    private $boxofficePurchaseMapper;
    private $customerPurchaseMapper;
    private $reservationMapper;
    private $reservationConverter;
    private $token;
    private $uuidFactory;
    private $log;
    private $settings;
    
    public function __construct(
        \Spot\MapperInterface $orderMapper,
        \Spot\MapperInterface $boxofficePurchaseMapper,
        \Spot\MapperInterface $customerPurchaseMapper,
        \Spot\MapperInterface $reservationMapper,
        ReservationConverterInterface $reservationConverter,
        TokenProviderInterface $tokenProvider,
        \Ramsey\Uuid\UuidFactoryInterface $uuidFactory,
        LogInterface $log,
        $settings) {
            $this->orderMapper = $orderMapper;
            $this->boxofficePurchaseMapper = $boxofficePurchaseMapper;
            $this->customerPurchaseMapper = $customerPurchaseMapper;
            $this->reservationMapper = $reservationMapper;
            $this->reservationConverter = $reservationConverter;
            $this->token = $tokenProvider->provide();
            $this->uuidFactory = $uuidFactory;
            $this->log = $log;
            $this->settings = $settings;
    }

    public function getReservations() {
        $this->deleteStaleReservations();
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        return $expandedReservations;
    }

    public function reserve($seat, $event, $category) {
        $this->deleteStaleReservations();
        $oldestReservation = $this->reservationMapper
            ->where([ 'token' => $this->token, 'order_id' => null ])
            ->order([ 'timestamp' => 'DESC' ])
            ->first();
        $timestamp = $oldestReservation != null ? $oldestReservation->get('timestamp') : time();
        $data = [
            'unique_id' => $this->uuidFactory->uuid1(),
            'token' => $this->token,
            'seat_id' => $seat->get('id'),
            'event_id' => $event->get('id'),
            'category_id' => $category->get('id'),
            'timestamp' => $timestamp,
            'is_reduced' => false,
            'is_scanned' => false
        ];
        $result = $this->reservationMapper->insert($data);
        if ($result) {
            return $this->reservationMapper->get($result);
        } else {
            $this->log->info('Seat reservation conflict. Seat: ' . $seat->get('id') . '|' . $seat->get('name') . ', Event: ' . $event->get('id') . '|' . $event->get('name'));
            return null;
        }
    }

    public function release($reservationId) {
        $this->reservationMapper->delete([ 'id' => $reservationId, 'token' => $this->token ]);
    }

    public function getReservationsExpirationTimestamp() {
        $this->deleteStaleReservations();
        $oldestReservation = $this->reservationMapper
            ->where([ 'token' => $this->token, 'order_id' => null ])
            ->order([ 'timestamp' => 'DESC' ])
            ->first();
        if ($oldestReservation != null) {
            return $oldestReservation->get('timestamp') + $this->settings['lifetimeInSeconds'];
        } else {
            return null;
        }
    }

    public function changeReduction($reservationId, $value) {
        $this->deleteStaleReservations();
        $reservation = $this->reservationMapper->first([ 'id' => $reservationId, 'token' => $this->token, 'order_id' => null]);
        if ($reservation != null) {
            $reservation->is_reduced = $value;
            $this->reservationMapper->update($reservation);
        } else {
            $this->log->info('Reservation lost when trying to change reduction.');
        }
        return $reservation;
    }

    public function order($title, $firstname, $lastname, $email, $locale) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $data = [
                'unique_id' => $this->uuidFactory->uuid1(),
                'title' => $title,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'locale' => $locale,
                'timestamp' => time()
            ];
            $order = $this->orderMapper->create($data);
            $order->reservations = $this->reservationConverter->convert($reservations);
            foreach ($reservations as $reservation) {
                $reservation->order_kind = 'reservation';
                $reservation->order_id = $order->get('id');
                $this->reservationMapper->update($reservation);
            }
            $this->log->info('Created order with ' . count($reservations) . ' seats.');
            return $order;
        } else {
            $this->log->info('Reservations lost when trying to create order.');
            return null;
        }
    }

    public function boxofficePurchase($boxofficeName, $locale) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $expandedReservations = $this->reservationConverter->convert($reservations);
            $totalPrice = $this->getTotalPriceOfExpandedReservations($expandedReservations);
            $data = [
                'unique_id' => $this->uuidFactory->uuid1(),
                'boxoffice' => $boxofficeName,
                'price' => $totalPrice,
                'locale' => $locale,
                'is_printed' => false,
                'timestamp' => time()
            ];
            $purchase = $this->boxofficePurchaseMapper->create($data);
            foreach ($reservations as $reservation) {
                $reservation->order_kind = 'boxoffice-purchase';
                $reservation->order_id = $purchase->get('id');
                $this->reservationMapper->update($reservation);
            }

            foreach($expandedReservations as $k => $v) {
                $expandedReservations[$k]->order_id = $purchase->get('id');
            }
            $purchase->reservations = $expandedReservations;
            
            $this->log->info('Created boxoffice purchase with ' . count($expandedReservations) . ' seats.');
            return $purchase;
        } else {
            $this->log->info('Reservations lost when trying to create boxoffice purchase.');
            return null;
        }
    }

    public function customerPurchase($title, $firstname, $lastname, $email, $locale) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $expandedReservations = $this->reservationConverter->convert($reservations);
            $totalPrice = $this->getTotalPriceOfExpandedReservations($expandedReservations);
            $data = [
                'unique_id' => $this->uuidFactory->uuid1(),
                'title' => $title,
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'price' => $totalPrice,
                'locale' => $locale,
                'timestamp' => time()
            ];
            $purchase = $this->customerPurchaseMapper->create($data);
            $purchase->reservations = $expandedReservations;
            foreach ($reservations as $reservation) {
                $reservation->order_kind = 'customer-purchase';
                $reservation->order_id = $purchase->get('id');
                $this->reservationMapper->update($reservation);
            }
            $this->log->info('Created customer purchase for ' . $email . ' with ' . count($expandedReservations) . ' seats.');
            return $purchase;
        } else {
            $this->log->info('Reservations lost when trying to create customer purchase.');
            return null;
        }
    }

    public function getTotalPriceOfPendingReservations() {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        $totalPrice = $this->getTotalPriceOfExpandedReservations($expandedReservations);
        return $totalPrice;
    }

    private function getTotalPriceOfExpandedReservations($expandedReservations) {
        $totalPrice = 0;
        foreach ($expandedReservations as $expandedReservation) {
            $totalPrice += $expandedReservation->price;
        }
        return $totalPrice;
    }

    private function deleteStaleReservations() {
        $oldestLockTime = time() - $this->settings['lifetimeInSeconds'];
        $this->reservationMapper->delete(['timestamp :lt' => $oldestLockTime, 'order_id' => null]);
    }
}