<?php

class PurchaseActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        
        $mailMock = $this->getMockBuilder(MailInterface::class)
            ->setMethods(['sendBoxofficePurchaseNotification'])
            ->getMock();
        $this->container['mail'] = $mailMock;
        
        $reserverMock = $this->getMockBuilder(SeatReserverInterface::class)
            ->setMethods(['boxofficePurchase'])
            ->getMock();
        $reserverMock
            ->method('boxofficePurchase')
            ->willReturn(new PurchaseActionsTestBoxofficePurchaseStub());
        $this->container['seatReserver'] = $reserverMock;

        $reservationConverterMock = $this->getMockBuilder(ReservationConverterInterface::class)
            ->setMethods(['convert'])
            ->getMock();
        $this->container['reservationConverter'] = $reservationConverterMock;
    }

    public function testExpandAllReservationsWhenListingWithoutEventId() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListBoxofficePurchasesAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, []);
    }

    public function testExpandEvent1ReservationsWhenListingWithEventId1() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListBoxofficePurchasesAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases?event_id=1');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, []);
    }

    public function testExpandNoReservationsWhenListingWithEventId2() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);
        $action = new Actions\ListBoxofficePurchasesAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases?event_id=2');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->never())->method('convert');
        $action($request, $response, []);
    }

    public function testSumUpReservationsPriceWhenListing() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([
                new PurchaseActionsTestExpandedReservationStub(2),
                new PurchaseActionsTestExpandedReservationStub(40)
            ]);
        
        $action = new Actions\ListBoxofficePurchasesAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);

        $decodedResponse = json_decode((string)$response->getBody(), true);
        $this->assertSame(1, count($decodedResponse));
        $this->assertSame(42, $decodedResponse[0]['totalPrice']);
    }

    public function testUseReserverToCreateBoxofficePurchase() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $data = [
            "boxoffice" => "Box office",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchase', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('boxofficePurchase');
        $action($request, $response, []);
    }

    public function testSendPurchaseNotification() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $data = [
            "boxoffice" => "Box office",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchase', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendBoxofficePurchaseNotification');
        $action($request, $response, []);
    }
}

class PurchaseActionsTestBoxofficePurchaseStub {
    public $reservations;

    public function __construct() {
        $this->reservations = [
            new PurchaseActionsTestExpandedReservationStub(1)
        ];
    }
}

class PurchaseActionsTestExpandedReservationStub {
    public $price;

    public function __construct($price) {
        $this->price = $price;
    }
}