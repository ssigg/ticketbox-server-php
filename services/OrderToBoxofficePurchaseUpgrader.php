<?php

namespace Services;

interface OrderToBoxofficePurchaseUpgraderInterface {
    function upgrade($order, $boxofficeName, $locale);
}

class OrderToBoxofficePurchaseUpgrader implements OrderToBoxofficePurchaseUpgraderInterface {
    private $orderMapper;
    private $boxofficePurchaseMapper;
    private $reservationMapper;
    private $reservationConverter;
    private $priceModificators;

    public function __construct(
        \Spot\MapperInterface $orderMapper,
        \Spot\MapperInterface $boxofficePurchaseMapper,
        \Spot\MapperInterface $reservationMapper,
        ReservationConverterInterface $reservationConverter,
        $priceModificators) {
        $this->orderMapper = $orderMapper;
        $this->boxofficePurchaseMapper = $boxofficePurchaseMapper;
        $this->reservationMapper = $reservationMapper;
        $this->reservationConverter = $reservationConverter;
        $this->priceModificators = $priceModificators;
    }

    public function upgrade($order, $boxofficeName, $locale) {
        $reservations = $this->reservationMapper->where([ 'order_id' => $order->get('id'), 'order_kind' => 'reservation' ]);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        $totalPrice = 0;
        foreach ($expandedReservations as $expandedReservation) {
            $totalPrice += $expandedReservation->price;
        }
        $data = [
            'boxoffice' => $boxofficeName,
            'price' => $totalPrice,
            'locale' => $locale,
            'timestamp' => time()
        ];
        $purchase = $this->boxofficePurchaseMapper->create($data);
        foreach ($reservations as $reservation) {
            $reservation->order_kind = 'boxoffice-purchase';
            $reservation->order_id = $purchase->get('id');
            $this->reservationMapper->update($reservation);
        }
        $this->orderMapper->delete($order->get('id'));

        $expandedReservations = $this->reservationConverter->convert($reservations);
        $purchase->reservations = $expandedReservations;
        return $purchase;
    }
}