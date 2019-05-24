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

class ListThinBoxofficePurchasesAction {
    private $orm;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $boxofficePurchaseMapper = $this->orm->mapper('Model\BoxofficePurchase');
        $reservationMapper = $this->orm->mapper('Model\Reservation');

        $boxofficeName = $args['boxoffice_name'];
        $boxofficePurchases = $boxofficePurchaseMapper->where([ 'boxoffice' => $boxofficeName ]);

        $eventId = $request->getQueryParam('event_id', null);
        $thinBoxofficePurchases = [];
        foreach ($boxofficePurchases as $boxofficePurchase) {
            if (!$boxofficePurchase->get('is_printed')) {
                $reservationPredicate = [ 'order_id' => $boxofficePurchase->id, 'order_kind' => 'boxoffice-purchase' ];
                if ($eventId != null) {
                    $reservationPredicate['event_id'] = $eventId;
                }
                $reservations = $reservationMapper->where($reservationPredicate);
                $tickets = [];
                foreach ($reservations as $reservation) {
                    $tickets[] = $reservation->unique_id;
                }
                $thinBoxofficePurchases[] = [
                    'id' => $boxofficePurchase->id,
                    'tickets' => $tickets
                ];
            }
        }

        return $response->withJson($thinBoxofficePurchases, 200);
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
    private $pdfTicketWriter;
    private $pdfTicketMerger;
    private $reserver;

    public function __construct(ContainerInterface $container) {
        $this->mail = $container->get('mail');
        $this->pdfTicketWriter = $container->get('pdfTicketWriter');
        $this->pdfTicketMerger = $container->get('pdfTicketMerger');
        $this->reserver = $container->get('seatReserver');
        $this->boxofficeSettings = $container->get('settings')['boxoffice'];
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $boxofficeName = $data['boxofficeName'];
        $boxofficeType = $data['boxofficeType']; // [paper|pdf|printout|download]
        $customerEmail = isset($data['email']) ? $data['email'] : null;
        $locale = $data['locale'];
        
        $purchase = $this->reserver->boxofficePurchase($boxofficeName, $locale);

        $totalPrice = 0;
        foreach ($purchase->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        if ($boxofficeType == 'pdf') {
            $this->mail->sendBoxofficePurchaseConfirmation($boxofficeName, $customerEmail, $locale, $purchase->reservations, $totalPrice);
        } else if ($boxofficeType == 'printout') {
            $this->pdfTicketWriter->write($purchase->reservations, true, $locale);
        } else if ($boxofficeType == 'download') {
            $pdfFilePaths = $this->pdfTicketWriter->write($purchase->reservations, true, $locale);
            $this->pdfTicketMerger->merge($pdfFilePaths, $purchase->unique_id . '.pdf');
        } else {
            // Neither a mail has to be delivered nor tickets have to be created
        }

        $this->mail->sendBoxofficePurchaseNotification($boxofficeName, $purchase->reservations, $totalPrice);

        return $response->withJson($purchase, 201);
    }
}

class UpgradeOrderToBoxofficePurchaseAction {
    private $orm;
    private $mail;
    private $pdfTicketWriter;
    private $pdfTicketMerger;
    private $orderToBoxofficePurchaseUpgrader;

    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
        $this->mail = $container->get('mail');
        $this->pdfTicketWriter = $container->get('pdfTicketWriter');
        $this->pdfTicketMerger = $container->get('pdfTicketMerger');
        $this->orderToBoxofficePurchaseUpgrader = $container->get('orderToBoxofficePurchaseUpgrader');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $mapper = $this->orm->mapper('Model\Order');
        $order = $mapper->get($args['id']);

        $data = $request->getParsedBody();
        $eventId = $data['eventId'];
        $boxofficeName = $data['boxofficeName'];
        $boxofficeType = $data['boxofficeType']; // [paper|pdf|printout|download]
        $customerEmail = $order->email;
        $locale = $data['locale'];

        $purchase = $this->orderToBoxofficePurchaseUpgrader->upgrade($order, $eventId, $boxofficeName, $locale);

        $totalPrice = 0;
        foreach ($purchase->reservations as $reservation) {
            $totalPrice += $reservation->price;
        }

        if ($boxofficeType == 'pdf') {
            $this->mail->sendBoxofficePurchaseConfirmation($boxofficeName, $customerEmail, $locale, $purchase->reservations, $totalPrice);
        } else if ($boxofficeType == 'printout') {
            $this->pdfTicketWriter->write($purchase->reservations, true, $locale);
        } else if ($boxofficeType == 'download') {
            $pdfFilePaths = $this->pdfTicketWriter->write($purchase->reservations, true, $locale);
            $this->pdfTicketMerger->merge($pdfFilePaths, $purchase->unique_id . '.pdf');
        } else {
            // Neither a mail has to be delivered nor tickets have to be created
        }

        $this->mail->sendBoxofficePurchaseNotification($boxofficeName, $purchase->reservations, $totalPrice);

        return $response->withJson($purchase, 200);
    }
}

class MarkBoxofficePurchasePrintStatusAction {
    private $orm;
    
    public function __construct(ContainerInterface $container) {
        $this->orm = $container->get('orm');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $boxofficePurchaseMapper = $this->orm->mapper('Model\BoxofficePurchase');
        $boxofficePurchase = $boxofficePurchaseMapper->get($args['id']);

        $data = $request->getParsedBody();
        $boxofficePurchase->is_printed = $data['isPrinted'];
        $boxofficePurchaseMapper->update($boxofficePurchase);

        return $response->withStatus(200);
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

class GetPdfTicketAction {
    private $ticketDirectoryPath;
    private $filePersister;

    public function __construct($container) {
        $pathConverter = $container->get('pathConverter');
        $this->ticketDirectoryPath = $pathConverter->convert($container->get('settings')['ticketDirectory']);
        $this->filePersister = $container->get('filePersister');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $uniqueId = $args['unique_id'];
        $filePath = $this->ticketDirectoryPath . '/' . $uniqueId . '.pdf';
        if ($this->filePersister->exists($filePath)) {
            // See http://discourse.slimframework.com/t/slim-3-download-files/224/2
            $fh = fopen($filePath, 'rb');

            $stream = new \Slim\Http\Stream($fh); // create a stream instance for the response body

            return $response->withHeader('Content-Type', 'application/force-download')
                            ->withHeader('Content-Type', 'application/octet-stream')
                            ->withHeader('Content-Type', 'application/download')
                            ->withHeader('Content-Description', 'File Transfer')
                            ->withHeader('Content-Transfer-Encoding', 'binary')
                            ->withHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"')
                            ->withHeader('Expires', '0')
                            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                            ->withHeader('Pragma', 'public')
                            ->withBody($stream); // all stream contents will be sent to the response
        } else {
            return $response->withStatus(404);
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