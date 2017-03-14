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
    private $settings;
    
    public function __construct(
        \Spot\MapperInterface $orderMapper,
        \Spot\MapperInterface $boxofficePurchaseMapper,
        \Spot\MapperInterface $customerPurchaseMapper,
        \Spot\MapperInterface $reservationMapper,
        ReservationConverterInterface $reservationConverter,
        TokenProviderInterface $tokenProvider,
        $settings) {
            $this->orderMapper = $orderMapper;
            $this->boxofficePurchaseMapper = $boxofficePurchaseMapper;
            $this->customerPurchaseMapper = $customerPurchaseMapper;
            $this->reservationMapper = $reservationMapper;
            $this->reservationConverter = $reservationConverter;
            $this->token = $tokenProvider->provide();
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
            'unique_id' => bin2hex(openssl_random_pseudo_bytes(8)),
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
        }
        return $reservation;
    }

    public function order($title, $firstname, $lastname, $email, $locale) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $data = [
                'unique_id' => bin2hex(openssl_random_pseudo_bytes(8)),
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
            return $order;
        } else {
            return null;
        }
    }

    public function boxofficePurchase($boxofficeName, $locale) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $expandedReservations = $this->reservationConverter->convert($reservations);
            $totalPrice = $this->getTotalPriceOfExpandedReservations($expandedReservations);
            $data = [
                'unique_id' => bin2hex(openssl_random_pseudo_bytes(8)),
                'boxoffice' => $boxofficeName,
                'price' => $totalPrice,
                'locale' => $locale,
                'timestamp' => time()
            ];
            $purchase = $this->boxofficePurchaseMapper->create($data);
            $purchase->reservations = $expandedReservations;
            foreach ($reservations as $reservation) {
                $reservation->order_kind = 'boxoffice-purchase';
                $reservation->order_id = $purchase->get('id');
                $this->reservationMapper->update($reservation);
            }
            return $purchase;
        } else {
            return null;
        }
    }

    public function customerPurchase($title, $firstname, $lastname, $email, $locale) {
        $reservations = $this->reservationMapper->where([ 'token' => $this->token, 'order_id' => null ]);
        if (count($reservations) > 0) {
            $expandedReservations = $this->reservationConverter->convert($reservations);
            $totalPrice = $this->getTotalPriceOfExpandedReservations($expandedReservations);
            $data = [
                'unique_id' => bin2hex(openssl_random_pseudo_bytes(8)),
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
            return $purchase;
        } else {
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