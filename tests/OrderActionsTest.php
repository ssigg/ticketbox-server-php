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
            ->willReturn(new OrderStub());
        $this->container['seatReserver'] = $reserverMock;
    }

    public function testUseReserverToCreateOrder() {
        $action = new Actions\CreateOrderAction($this->container);

        $data = [
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com"
        ];
        $request = $this->getPostRequest('/events', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('order');
        $action($request, $response, []);
    }

    public function testSendOrderConfirmation() {
        $action = new Actions\CreateOrderAction($this->container);

        $data = [
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com"
        ];
        $request = $this->getPostRequest('/events', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendOrderConfirmation');
        $action($request, $response, []);
    }

    public function testSendOrderNotification() {
        $action = new Actions\CreateOrderAction($this->container);

        $data = [
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com"
        ];
        $request = $this->getPostRequest('/events', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendOrderNotification');
        $action($request, $response, []);
    }
}

class OrderStub {
    public $reservations;

    public function __construct() {
        $this->reservations = [
            new ExpandedReservationMock()
        ];
    }
}

class ExpandedReservationMock {
    public $price;

    public function __construct() {
        $this->price = 1;
    }
}