<?php

class PurchaseActionsTest extends DatabaseTestBase {
    protected function setUp() {
        parent::setUp();
        
        $mailMock = $this->getMockBuilder(MailInterface::class)
            ->setMethods([
                'sendBoxofficePurchaseNotification',
                'sendBoxofficePurchaseConfirmation',
                'sendCustomerPurchaseNotification',
                'sendCustomerPurchaseConfirmation'])
            ->getMock();
        $this->container['mail'] = $mailMock;

        $pdfTicketWriterMock = $this->getMockBuilder(PdfTicketWriterInterface::class)
            ->setMethods(['write'])
            ->getMock();
        $this->container['pdfTicketWriter'] = $pdfTicketWriterMock;

        $pdfTicketMergerMock = $this->getMockBuilder(PdfTicketMergerInterface::class)
            ->setMethods(['merge'])
            ->getMock();
        $this->container['pdfTicketMerger'] = $pdfTicketMergerMock;
        
        $reserverMock = $this->getMockBuilder(SeatReserverInterface::class)
            ->setMethods(['boxofficePurchase', 'customerPurchase', 'getTotalPriceOfPendingReservations'])
            ->getMock();
        $reserverMock
            ->method('boxofficePurchase')
            ->willReturn(new PurchaseActionsTestBoxofficePurchaseStub());
        $reserverMock
            ->method('customerPurchase')
            ->willReturn(new PurchaseActionsTestCustomerPurchaseStub());
        $this->container['seatReserver'] = $reserverMock;

        $upgraderMock = $this->getMockBuilder(OrderToBoxofficePurchaseUpgraderInterface::class)
            ->setMethods(['upgrade'])
            ->getMock();
        $upgraderMock
            ->method('upgrade')
            ->willReturn(new PurchaseActionsTestBoxofficePurchaseStub());
        $this->container['orderToBoxofficePurchaseUpgrader'] = $upgraderMock;

        $reservationConverterMock = $this->getMockBuilder(ReservationConverterInterface::class)
            ->setMethods(['convert'])
            ->getMock();
        $this->container['reservationConverter'] = $reservationConverterMock;

        $paymentProviderMock = $this->getMockBuilder(PaymentProviderMockInterface::class)
            ->setMethods(['getToken', 'sale'])
            ->getMock();
        $this->container['paymentProvider'] = $paymentProviderMock;
    }

    public function testExpandAllReservationsWhenListingBoxofficePurchasesWithoutEventId() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
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

    public function testExpandEvent1ReservationsWhenListingBoxofficePurchasesWithEventId1() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
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

    public function testExpandNoReservationsWhenListingBoxofficePurchasesWithEventId2() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
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

    public function testSumUpReservationsPriceWhenListingBoxofficePurchases() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
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

    public function testListThinBoxofficePurchases() {
        $boxofficePurchaseMapper = $this->container->get('orm')->mapper('Model\BoxofficePurchase');
        $action = new Actions\ListThinBoxofficePurchasesAction($this->container);

        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $request = $this->getGetRequest('/boxoffice-purchases/Box%20Office');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'boxoffice_name' => 'Box Office' ]);
        $this->assertSame(
            '[{"id":1,"tickets":["unique_base_2"]}]',
            (string)$response->getBody());
    }

    public function testListThinBoxofficePurchaseListsOnlyUnprintedBoxofficePurchases() {
        $boxofficePurchaseMapper = $this->container->get('orm')->mapper('Model\BoxofficePurchase');
        $boxofficePurchaseMapper->create([
            'unique_id' => 'boxoffice-unique_base',
            'boxoffice' => 'Box Office',
            'price' => 42,
            'locale' => 'en',
            'is_printed' => true,
            'timestamp' => 123
        ]);
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $action = new Actions\ListThinBoxofficePurchasesAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases/Box%20Office');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'boxoffice_name' => 'Box Office' ]);
        $this->assertSame(
            '[{"id":1,"tickets":["unique_base_2"]}]',
            (string)$response->getBody());
    }

    public function testListThinBoxofficePurchasesWithEventIdListsOnlyTicketsOfEvent() {
        $boxofficePurchaseMapper = $this->container->get('orm')->mapper('Model\BoxofficePurchase');
        $action = new Actions\ListThinBoxofficePurchasesAction($this->container);

        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 2,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);
        $reservationMapper->create([
            'unique_id' => 'unique_base_3',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $request = $this->getGetRequest('/boxoffice-purchases/Box%20Office?event_id=1');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, [ 'boxoffice_name' => 'Box Office' ]);
        $this->assertSame(
            '[{"id":1,"tickets":["unique_base_3"]}]',
            (string)$response->getBody());
    }

    public function testGetBoxofficePurchaseWithKnownUniqueIdConvertsReservations() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\GetBoxofficePurchaseAction($this->container);

        $request = $this->getGetRequest('/boxxoffice-purchases/boxoffice-unique_base');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, [ 'unique_id' => 'boxoffice-unique_base' ]);
    }

    public function testGetBoxofficePurchaseWithUnknownUniqueIdDoesNotConvertReservations() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\GetBoxofficePurchaseAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases/unknown');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->never())->method('convert');
        $action($request, $response, [ 'unique_id' => 'unknown' ]);
    }

    public function testUseReserverToCreateBoxofficePurchase() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "paper",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchases', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');

        $reserverMock->expects($this->once())->method('boxofficePurchase');
        $action($request, $response, []);
    }

    public function testSendBoxofficePurchaseNotification() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "paper",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchases', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendBoxofficePurchaseNotification');
        $action($request, $response, []);
    }

    public function testSendBoxofficePurchaseConfirmationWhenBoxofficeIsPdfBoxoffice() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "pdf",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchases', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendBoxofficePurchaseConfirmation');
        $action($request, $response, []);
    }

    public function testWriteTicketWhenBoxofficeIsPrintoutBoxoffice() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "printout",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchases', $data);
        $response = new \Slim\Http\Response();

        $pdfTicketWriterMock = $this->container->get('pdfTicketWriter');

        $pdfTicketWriterMock->expects($this->once())->method('write');
        $action($request, $response, []);
    }

    public function testWriteAndMergeTicketWhenBoxofficeIsDownloadBoxoffice() {
        $action = new Actions\CreateBoxofficePurchaseAction($this->container);

        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "download",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/boxoffice-purchases', $data);
        $response = new \Slim\Http\Response();

        $pdfTicketWriterMock = $this->container->get('pdfTicketWriter');
        $pdfTicketMergerMock = $this->container->get('pdfTicketMerger');

        $pdfTicketWriterMock->expects($this->once())->method('write');
        $pdfTicketMergerMock->expects($this->once())->method('merge');
        $action($request, $response, []);
    }

    public function testUseUpgraderToUpgradeOrderToBoxofficePurchase() {
        $action = new Actions\UpgradeOrderToBoxofficePurchaseAction($this->container);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "paper",
            "locale" => "en"
        ];
        $request = $this->getPutRequest('/upgrade-order/1', $data);
        $response = new \Slim\Http\Response();

        $upgraderMock = $this->container->get('orderToBoxofficePurchaseUpgrader');

        $upgraderMock->expects($this->once())->method('upgrade');
        $action($request, $response, [ 'id' => 1 ]);
    }

    public function testSendBoxofficePurchaseNotificationWhenUpgradingOrder() {
        $action = new Actions\UpgradeOrderToBoxofficePurchaseAction($this->container);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "paper",
            "locale" => "en"
        ];
        $request = $this->getPutRequest('/upgrade-order/1', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendBoxofficePurchaseNotification');
        $action($request, $response, [ 'id' => 1 ]);
    }

    public function testSendBoxofficePurchaseConfirmationWhileUpgradingOrderWhenBoxofficeIsPdfBoxoffice() {
        $action = new Actions\UpgradeOrderToBoxofficePurchaseAction($this->container);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "pdf",
            "locale" => "en"
        ];
        $request = $this->getPutRequest('/upgrade-order/1', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');

        $mailMock->expects($this->once())->method('sendBoxofficePurchaseConfirmation');
        $action($request, $response, [ 'id' => 1 ]);
    }

    public function testWriteTicketWhileUpgradingOrderWhenBoxofficeIsPrintoutBoxoffice() {
        $action = new Actions\UpgradeOrderToBoxofficePurchaseAction($this->container);

        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "printout",
            "locale" => "en"
        ];
        $request = $this->getPutRequest('/upgrade-order/1', $data);
        $response = new \Slim\Http\Response();

        $pdfTicketWriterMock = $this->container->get('pdfTicketWriter');

        $pdfTicketWriterMock->expects($this->once())->method('write');
        $action($request, $response, [ 'id' => 1 ]);
    }

    public function testWriteAndMergeTicketWhileUpgradingOrderWhenBoxofficeIsDownloadBoxoffice() {
        $action = new Actions\UpgradeOrderToBoxofficePurchaseAction($this->container);

        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');
        $reservationMapper->create([
            'unique_id' => 'unique_base_2',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'boxoffice-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $data = [
            "boxofficeName" => "Box Office",
            "boxofficeType" => "download",
            "locale" => "en"
        ];
        $request = $this->getPutRequest('/upgrade-order/1', $data);
        $response = new \Slim\Http\Response();

        $pdfTicketWriterMock = $this->container->get('pdfTicketWriter');
        $pdfTicketMergerMock = $this->container->get('pdfTicketMerger');

        $pdfTicketWriterMock->expects($this->once())->method('write');
        $pdfTicketMergerMock->expects($this->once())->method('merge');
        $action($request, $response, [ 'id' => 1 ]);
    }

    public function testMarkBoxofficePurchasePrintStatus() {
        $action = new Actions\MarkBoxofficePurchasePrintStatusAction($this->container);

        $boxofficePurchaseMapper = $this->container->get('orm')->mapper('Model\BoxofficePurchase');

        $data = [
            "isPrinted" => true
        ];
        $request = $this->getPutRequest('/boxoffice-purchases/1', $data);
        $response = new \Slim\Http\Response();

        $action($request, $response, [ 'id' => 1 ]);
        $boxofficePurchase = $boxofficePurchaseMapper->get(1);
        $this->assertSame(true, $boxofficePurchase->is_printed);
    }

    public function testExpandAllReservationsWhenListingCustomerPurchasesWithoutEventId() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'customer-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListCustomerPurchasesAction($this->container);

        $request = $this->getGetRequest('/customer-purchases');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, []);
    }

    public function testExpandEvent1ReservationsWhenListingCustomerPurchasesWithEventId1() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'customer-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\ListCustomerPurchasesAction($this->container);

        $request = $this->getGetRequest('/boxoffice-purchases?event_id=1');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, []);
    }

    public function testExpandNoReservationsWhenListingCustomerPurchasesWithEventId2() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'customer-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);
        $action = new Actions\ListCustomerPurchasesAction($this->container);

        $request = $this->getGetRequest('/customer-purchases?event_id=2');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->never())->method('convert');
        $action($request, $response, []);
    }

    public function testSumUpReservationsPriceWhenListingCustomerPurchases() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationMapper->create([
            'unique_id' => 'unique',
            'token' => 'abc',
            'seat_id' => 2,
            'event_id' => 1,
            'category_id' => 1,
            'order_id' => 1,
            'order_kind' => 'customer-purchase',
            'is_reduced' => false,
            'is_scanned' => false,
            'timestamp' => time()]);

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([
                new PurchaseActionsTestExpandedReservationStub(2),
                new PurchaseActionsTestExpandedReservationStub(40)
            ]);
        
        $action = new Actions\ListCustomerPurchasesAction($this->container);

        $request = $this->getGetRequest('/customer-purchases');
        $response = new \Slim\Http\Response();

        $response = $action($request, $response, []);

        $decodedResponse = json_decode((string)$response->getBody(), true);
        $this->assertSame(1, count($decodedResponse));
        $this->assertSame(42, $decodedResponse[0]['totalPrice']);
    }

    public function testGetCustomerPurchaseWithKnownUniqueIdConvertsReservations() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\GetCustomerPurchaseAction($this->container);

        $request = $this->getGetRequest('/customer-purchases/boxoffice-unique_base');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->once())->method('convert');
        $action($request, $response, [ 'unique_id' => 'customer-unique_base' ]);
    }

    public function testGetCustomerPurchaseWithUnknownUniqueIdDoesNotConvertReservations() {
        $reservationMapper = $this->container->get('orm')->mapper('Model\Reservation');

        $reservationConverterMock = $this->container->get('reservationConverter');
        $reservationConverterMock
            ->method('convert')
            ->willReturn([]);

        $action = new Actions\GetCustomerPurchaseAction($this->container);

        $request = $this->getGetRequest('/customer-purchases/unknown');
        $response = new \Slim\Http\Response();

        $reservationConverterMock = $this->container->get('reservationConverter');

        $reservationConverterMock->expects($this->never())->method('convert');
        $action($request, $response, [ 'unique_id' => 'unknown' ]);
    }

    public function testUsePaymentProviderToGetToken() {
        $action = new Actions\GetCustomerPurchaseTokenAction($this->container);

        $request = $this->getGetRequest('/customer-purchase-token');
        $response = new \Slim\Http\Response();

        $paymentProviderMock = $this->container->get('paymentProvider');

        $paymentProviderMock->expects($this->once())->method('getToken');
        $action($request, $response, []);
    }

    public function testUsePaymentProviderToSaleSuccessful() {
        $action = new Actions\CreateCustomerPurchaseAction($this->container);

        $data = [
            "nonce" => "<nonce>",
            "title" => "m",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/customer-purchases', $data);
        $response = new \Slim\Http\Response();

        $paymentProviderMock = $this->container->get('paymentProvider');
        $paymentProviderMock
            ->method('sale')
            ->willReturn(new PurchaseActionsTestSaleResultStub(true));

        $paymentProviderMock->expects($this->once())->method('sale');
        $returnValue = $action($request, $response, []);
        $this->assertSame(201, $returnValue->getStatusCode());
    }

    public function testUsePaymentProviderToSaleFailure() {
        $action = new Actions\CreateCustomerPurchaseAction($this->container);

        $data = [
            "nonce" => "<nonce>",
            "title" => "m",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/customer-purchases', $data);
        $response = new \Slim\Http\Response();

        $paymentProviderMock = $this->container->get('paymentProvider');
        $paymentProviderMock
            ->method('sale')
            ->willReturn(new PurchaseActionsTestSaleResultStub(false));

        $paymentProviderMock->expects($this->once())->method('sale');
        $returnValue = $action($request, $response, []);
        $this->assertSame(400, $returnValue->getStatusCode());
    }

    public function testUseReserverToCreateCustomerPurchase() {
        $action = new Actions\CreateCustomerPurchaseAction($this->container);

        $data = [
            "nonce" => "<nonce>",
            "title" => "m",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/customer-purchases', $data);
        $response = new \Slim\Http\Response();

        $reserverMock = $this->container->get('seatReserver');
        $paymentProviderMock = $this->container->get('paymentProvider');
        $paymentProviderMock
            ->method('sale')
            ->willReturn(new PurchaseActionsTestSaleResultStub(true));

        $reserverMock->expects($this->once())->method('customerPurchase');
        $action($request, $response, []);
    }

    public function testSendCustomerPurchaseNotification() {
        $action = new Actions\CreateCustomerPurchaseAction($this->container);

        $data = [
            "nonce" => "<nonce>",
            "title" => "m",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/customer-purchases', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');
        $paymentProviderMock = $this->container->get('paymentProvider');
        $paymentProviderMock
            ->method('sale')
            ->willReturn(new PurchaseActionsTestSaleResultStub(true));

        $mailMock->expects($this->once())->method('sendCustomerPurchaseNotification');
        $action($request, $response, []);
    }

    public function testSendCustomerPurchaseConfirmation() {
        $action = new Actions\CreateCustomerPurchaseAction($this->container);

        $data = [
            "nonce" => "<nonce>",
            "title" => "m",
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => "john.doe@example.com",
            "locale" => "en"
        ];
        $request = $this->getPostRequest('/customer-purchases', $data);
        $response = new \Slim\Http\Response();

        $mailMock = $this->container->get('mail');
        $paymentProviderMock = $this->container->get('paymentProvider');
        $paymentProviderMock
            ->method('sale')
            ->willReturn(new PurchaseActionsTestSaleResultStub(true));

        $mailMock->expects($this->once())->method('sendCustomerPurchaseConfirmation');
        $action($request, $response, []);
    }
}

class PurchaseActionsTestBoxofficePurchaseStub {
    public $reservations;
    public $unique_id;

    public function __construct() {
        $this->reservations = [
            new PurchaseActionsTestExpandedReservationStub(1)
        ];
        $this->unique_id = 'unique_id';
    }
}

class PurchaseActionsTestCustomerPurchaseStub {
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

class PurchaseActionsTestSaleResultStub {
    public $success;

    public function __construct($success) {
        $this->success = $success;
    }
}