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
            ->willReturn(new BoxofficePurchaseStub());
        $this->container['seatReserver'] = $reserverMock;
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

class BoxofficePurchaseStub {
    public $reservations;

    public function __construct() {
        $this->reservations = [
            new PurchaseActionsTestExpandedReservationMock()
        ];
    }
}

class PurchaseActionsTestExpandedReservationMock {
    public $price;

    public function __construct() {
        $this->price = 1;
    }
}