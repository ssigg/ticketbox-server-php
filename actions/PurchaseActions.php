<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class ListBoxofficePurchasesAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $boxofficePurchaseMapper = $this->orm->mapper('Model\BoxofficePurchase');
        $reservationMapper = $this->orm->mapper('Model\Reservation');

        $boxofficePurchases = $boxofficePurchaseMapper->all();

        $eventId = $request->getQueryParam('event_id', null);
        $expandedBoxofficePurchases = [];
        foreach ($boxofficePurchases as $boxofficePurchase) {
            $reservations = [];
            if ($eventId != null) {
                $reservations = $reservationMapper->where([ 'order_id' => $boxofficePurchase->id, 'order_kind' => 'boxoffice-purchase', 'event_id' => $eventId ]);
            } else {
                $reservations = $reservationMapper->where([ 'order_id' => $boxofficePurchase->id, 'order_kind' => 'boxoffice-purchase' ]);
            }
            if (count($reservations) > 0) {
                $expandedReservations = $this->reservationConverter->convert($reservations);
                $expandedBoxofficePurchase = new ExpandedBoxofficePurchase($boxofficePurchase, $expandedReservations);
                $expandedBoxofficePurchases[] = $expandedBoxofficePurchase;
            }
        }

        return $response->withJson($expandedBoxofficePurchases, 200);
    }
}

class GetBoxofficePurchaseAction {
    private $orm;
    private $reservationConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $unique_id = $args['unique_id'];
        $boxofficePurchaseMapper = $this->orm->mapper('Model\BoxofficePurchase');
        $reservationMapper = $this->orm->mapper('Model\Reservation');
        $boxofficePurchase = $boxofficePurchaseMapper->first([ 'unique_id' => $unique_id ]);

        if ($boxofficePurchase != null) {
            $reservations = $reservationMapper->where([ 'order_id' => $boxofficePurchase->id, 'order_kind' => 'boxoffice-purchase' ]);
            $expandedReservations = $this->reservationConverter->convert($reservations);
            $expandedBoxofficePurchase = new ExpandedBoxofficePurchase($boxofficePurchase, $expandedReservations);
            return $response->withJson($expandedBoxofficePurchase, 200);
        } else {
            return $response->withStatus(404);
        }
    }
}

class CreateBoxofficePurchaseAction {
    private $mail;
    private $reserver;
    private $tempDirectory;

    public function __construct(ContainerInterface $container) {
        $this->mail = $container->get('mail');
        $this->reserver = $container->get('seatReserver');
        $this->boxofficeSettings = $container->get('settings')['boxoffice'];
        $this->tempDirectory = $container->get('settings')['tempDirectory'];
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $boxofficeName = $data['boxofficeName'];
        $boxofficeType = $data['boxofficeType']; // [paper|pdf]
        $customerEmail = isset($data['email']) ? $data['email'] : null;
        $locale = $data['locale'];
        
        $purchase = $this->reserver->boxofficePurchase($boxofficeName, $locale);

        $totalPrice = 0;
        foreach ($purchase->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        if ($boxofficeType == 'pdf') {
            $this->mail->sendBoxofficePurchaseConfirmation($boxofficeName, $customerEmail, $locale, $purchase->reservations, $totalPrice);
        }

        $this->mail->sendBoxofficePurchaseNotification($boxofficeName, $purchase->reservations, $totalPrice);

        return $response->withJson($purchase, 201);
    }
}

class UpgradeOrderToBoxofficePurchaseAction {
    private $orm;
    private $mail;
    private $orderToBoxofficePurchaseUpgrader;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->mail = $container->get('mail');
        $this->orderToBoxofficePurchaseUpgrader = $container->get('orderToBoxofficePurchaseUpgrader');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Order');
        $order = $mapper->get($args['id']);

        $data = $request->getParsedBody();
        $boxofficeName = $data['boxofficeName'];
        $boxofficeType = $data['boxofficeType']; // [paper|pdf]
        $customerEmail = $order->email;
        $locale = $data['locale'];

        $purchase = $this->orderToBoxofficePurchaseUpgrader->upgrade($order, $boxofficeName, $locale);

        $totalPrice = 0;
        foreach ($purchase->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        if ($boxofficeType == 'pdf') {
            $this->mail->sendBoxofficePurchaseConfirmation($boxofficeName, $customerEmail, $locale, $purchase->reservations, $totalPrice);
        }

        $this->mail->sendBoxofficePurchaseNotification($boxofficeName, $purchase->reservations, $totalPrice);

        return $response->withJson($purchase, 200);
    }
}

class ListCustomerPurchasesAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $customerPurchaseMapper = $this->orm->mapper('Model\CustomerPurchase');
        $reservationMapper = $this->orm->mapper('Model\Reservation');

        $customerPurchases = $customerPurchaseMapper->all();

        $eventId = $request->getQueryParam('event_id', null);
        $expandedCustomerPurchases = [];
        foreach ($customerPurchases as $customerPurchase) {
            $reservations = [];
            if ($eventId != null) {
                $reservations = $reservationMapper->where([ 'order_id' => $customerPurchase->id, 'order_kind' => 'customer-purchase', 'event_id' => $eventId ]);
            } else {
                $reservations = $reservationMapper->where([ 'order_id' => $customerPurchase->id, 'order_kind' => 'customer-purchase' ]);
            }
            if (count($reservations) > 0) {
                $expandedReservations = $this->reservationConverter->convert($reservations);
                $expandedCustomerPurchase = new ExpandedCustomerPurchase($customerPurchase, $expandedReservations);
                $expandedCustomerPurchases[] = $expandedCustomerPurchase;
            }
        }

        return $response->withJson($expandedCustomerPurchases, 200);
    }
}

class GetCustomerPurchaseAction {
    private $orm;
    private $reservationConverter;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->reservationConverter = $container->get('reservationConverter');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $unique_id = $args['unique_id'];
        $customerPurchaseMapper = $this->orm->mapper('Model\CustomerPurchase');
        $reservationMapper = $this->orm->mapper('Model\Reservation');
        $customerPurchase = $customerPurchaseMapper->first([ 'unique_id' => $unique_id ]);

        if ($customerPurchase != null) {
            $reservations = $reservationMapper->where([ 'order_id' => $customerPurchase->id, 'order_kind' => 'customer-purchase' ]);
            $expandedReservations = $this->reservationConverter->convert($reservations);
            $expandedCustomerPurchase = new ExpandedCustomerPurchase($customerPurchase, $expandedReservations);
            return $response->withJson($expandedCustomerPurchase, 200);
        } else {
            return $response->withStatus(404);
        }
    }
}

class GetCustomerPurchaseTokenAction {
    private $paymentProvider;

    public function __construct(ContainerInterface $container) {
        $this->paymentProvider = $container->get('paymentProvider');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $token = $this->paymentProvider->getToken();
        $tokenContainer = [ "value" => $token ];
        return $response->withJson($tokenContainer, 200);
    }
}

class CreateCustomerPurchaseAction {
    private $reserver;
    private $paymentProvider;
    private $mail;

    public function __construct(ContainerInterface $container) {
        $this->reserver = $container->get('seatReserver');
        $this->paymentProvider = $container->get('paymentProvider');
        $this->mail = $container->get('mail');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $nonce = $data['nonce'];
        $title = $data['title'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $email = $data['email'];
        $locale = $data['locale'];

        $totalPrice = $this->reserver->getTotalPriceOfPendingReservations();

        $result = $this->paymentProvider->sale($totalPrice, $nonce, $firstname, $lastname, $email);
        if ($result->success) {
            $purchase = $this->reserver->customerPurchase($title, $firstname, $lastname, $email, $locale);
            $this->mail->sendCustomerPurchaseConfirmation($purchase, $totalPrice);
            $this->mail->sendCustomerPurchaseNotification($purchase, $totalPrice);
            return $response->withJson($purchase, 201);
        } else {
            return $response->withStatus(400);
        }
    }
}

class ExpandedBoxofficePurchase {
    public $id;
    public $boxoffice;
    public $locale;
    public $timestamp;
    public $reservations;
    public $totalPrice;
    public function __construct($boxofficePurchase, $reservations) {
        $this->id = $boxofficePurchase->id;
        $this->boxoffice = $boxofficePurchase->boxoffice;
        $this->locale = $boxofficePurchase->locale;
        $this->timestamp = $boxofficePurchase->timestamp;
        $this->reservations = $reservations;

        $this->totalPrice = 0;
        foreach ($reservations as $reservation) {
            $this->totalPrice += $reservation->price; 
        }
    }
}

class ExpandedCustomerPurchase {
    public $id;
    public $title;
    public $firstname;
    public $lastname;
    public $email;
    public $locale;
    public $timestamp;
    public $reservations;
    public $totalPrice;
    public function __construct($customerPurchase, $reservations) {
        $this->id = $customerPurchase->id;
        $this->title = $customerPurchase->title;
        $this->firstname = $customerPurchase->firstname;
        $this->lastname = $customerPurchase->lastname;
        $this->email = $customerPurchase->email;
        $this->locale = $customerPurchase->locale;
        $this->timestamp = $customerPurchase->timestamp;
        $this->reservations = $reservations;

        $this->totalPrice = 0;
        foreach ($reservations as $reservation) {
            $this->totalPrice += $reservation->price; 
        }
    }
}