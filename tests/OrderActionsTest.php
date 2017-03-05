<?php

class OrderActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        
        $mailMock = $this->getMockBuilder(MailInterface::class)
            ->setMethods(['sendOrderNotification', 'sendOrderConfirmation'])
            ->getMock();
        $this->container['mail'] = $mailMock;
        
        $reserverMock = $this->getMockBuilder(SeatReserverInterface::class)
            ->setMethods(['order'])
            ->getMock();
        $reserverMock
            ->method('order')
            ->willReturn(new OrderActionsTestOrderStub());
        $this->container['seatReserver'] = $reserverMock;

        $reservationConverterMock = $this->getMockBuilder(ReservationConverterInterface::class)
            ->setMethods(['convert'])
            ->getMock();
        $this->container['reservationConverter'] = $reservationConverterMock;
    }

    public function testExpandAllReservationsWhenListingWithoutEventId() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'reservation',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);
        
        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListOrdersAction($this->container);

        $request = $this->getGetRequest('/orders');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, []);
    }

    public function testExpandEvent1ReservationsWhenListingWithEventId1() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'reservation',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListOrdersAction($this->container);

        $request = $this->getGetRequest('/orders?event_id=1');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, []);
    }

    public function testExpandNoReservationsWhenListingWithEventId2() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'reservation',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListOrdersAction($this->container);

        $request = $this->getGetRequest('/orders?event_id=2');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->never())->method('convert');
        $action($request, $response, []);
    }

    public function testSumUpReservationsPriceWhenListing() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'reservation',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([
                new OrderActionsTestExpandedReservationStub(2),
                new OrderActionsTestExpandedReservationStub(40)
            ]);
        
        $action = new Actions\ListOrdersAction($this->container);

        $request = $this->getGetRequest('/orders');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);

        $decodedResponse = json_decode((string)$response->getBody(), true);
        $this->assertSame(1, count($decodedResponse));
        $this->assertSame(42, $decodedResponse[0]['totalPrice']);
    }

    public function testUseReserverToCreateOrder() {
        $action = new Actions\CreateOrderAction($this->container);

        $data = [
            "title" => "Mr.",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/orders', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('order');
        $action($request, $response, []);
    }

    public function testSendOrderConfirmation() {
        $action = new Actions\CreateOrderAction($this->container);

        $data = [
            "title" => "Mr.",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/orders', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendOrderConfirmation');
        $action($request, $response, []);
    }

    public function testSendOrderNotification() {
        $action = new Actions\CreateOrderAction($this->container);

        $data = [
            "title" => "Mr.",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/orders', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendOrderNotification');
        $action($request, $response, []);
    }
}

class OrderActionsTestOrderStub {
    public $reservations;

    public function __construct() {
        $this->reservations = [
            new OrderActionsTestExpandedReservationStub(1)
        ];
    }
}

class OrderActionsTestExpandedReservationStub {
    public $price;

    public function __construct($price) {
        $this->price = $price;
    }
}