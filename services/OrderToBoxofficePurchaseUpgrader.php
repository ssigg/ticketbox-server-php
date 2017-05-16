<?php

namespace Services;

interface OrderToBoxofficePurchaseUpgraderInterface {
    function upgrade($order, $eventId, $boxofficeName, $locale);
}

class OrderToBoxofficePurchaseUpgrader implements OrderToBoxofficePurchaseUpgraderInterface {
    private $orderMapper;
    private $boxofficePurchaseMapper;
    private $reservationMapper;
    private $uuidFactory;
    private $reservationConverter;
    private $priceModificators;

    public function __construct(
        \Spot\MapperInterface $orderMapper,
        \Spot\MapperInterface $boxofficePurchaseMapper,
        \Spot\MapperInterface $reservationMapper,
        \Ramsey\Uuid\UuidFactoryInterface $uuidFactory,
        ReservationConverterInterface $reservationConverter,
        $priceModificators) {
        $this->orderMapper = $orderMapper;
        $this->boxofficePurchaseMapper = $boxofficePurchaseMapper;
        $this->reservationMapper = $reservationMapper;
        $this->uuidFactory = $uuidFactory;
        $this->reservationConverter = $reservationConverter;
        $this->priceModificators = $priceModificators;
    }

    public function upgrade($order, $eventId, $boxofficeName, $locale) {
        $reservationPredicate = [ 'order_id' => $order->get('id'), 'order_kind' => 'reservation' ];
        if ($eventId != null) {
            $reservationPredicate['event_id'] = $eventId;
        }
        $reservations = $this->reservationMapper->where($reservationPredicate);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        $totalPrice = 0;
        foreach ($expandedReservations as $expandedReservation) {
            $totalPrice += $expandedReservation->price;
        }
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
        $leftOverReservations = $this->reservationMapper->where([ 'order_id' => $order->get('id'), 'order_kind' => 'reservation' ]);
        if (count($leftOverReservations) == 0) {
            $this->orderMapper->delete([ 'id' => $order->get('id') ]);
        }

        $reservations = $this->reservationMapper->where([ 'order_id' => $purchase->get('id'), 'order_kind' => 'boxoffice-purchase' ]);
        $expandedReservations = $this->reservationConverter->convert($reservations);
        $purchase->reservations = $expandedReservations;
        return $purchase;
    }
}