<?php

class ScannerActionsTest extends \PHPUnit_Framework_TestCase {
    private $container;

    protected function setUp() {
        $this->container = new \Slim\Container;

        $ticketValidatorMock = $this->getMockBuilder(TicketValidatorInterface::class)
            ->setMethods(['validate'])
            ->getMock();
        $this->container['ticketValidator'] = $ticketValidatorMock;

        $ticketTestValidatorMock = $this->getMockBuilder(TicketValidatorInterface::class)
            ->setMethods(['validate'])
            ->getMock();
        $this->container['ticketTestValidator'] = $ticketTestValidatorMock;

        $pageMock = $this->getMockBuilder(PageInterface::class)
            ->setMethods(['getTicketValidatorResultPage'])
            ->getMock();
        $this->container['page'] = $pageMock;
    }

    public function codeProviderForTests() {
        return [ [ 'ok' ], [ 'error' ], [ 'warning' ] ];
    }
    /**
    * @dataProvider codeProviderForTests
    */
    public function testWhenTestCodeIsGivenTheTestValidatorIsUsed($code) {
        $action = new Actions\ValidateTicketAction($this->container);
        $key = 'key';
        $eventId = 'e1';
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/validate/' . $key . '/' . $eventId . '/' . $code
        ]);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        $ticketTestValidatorMock = $this->container->get('ticketTestValidator');
        $ticketTestValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($key), $this->equalTo($eventId), $this->equalTo($code));
        $action($request, $response, [ 'key' => $key, 'eventId' => $eventId, 'code' => $code ]);
    }

    public function codeProviderForRealCodes() {
        return [ [ 'nonTest1' ], [ 'nonTest2' ] ];
    }
    /**
    * @dataProvider codeProviderForRealCodes
    */
    public function testWhenNonTestCodeIsGivenTheRealValidatorIsUsed($code) {
        $action = new Actions\ValidateTicketAction($this->container);
        $key = 'key';
        $eventId = 'e1';
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/validate/' . $key . '/' . $eventId . '/' . $code
        ]);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        $ticketValidatorMock = $this->container->get('ticketValidator');
        $ticketValidatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($key), $this->equalTo($eventId), $this->equalTo($code));
        $action($request, $response, [ 'key' => $key, 'eventId' => $eventId, 'code' => $code ]);
    }

    public function testPageIsUsedToGenerateTheResultPage() {
        $action = new Actions\ValidateTicketAction($this->container);
        $key = 'key';
        $eventId = 'e1';
        $code = 'realCode';
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/validate/' . $key . '/' . $eventId . '/' . $code
        ]);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        $validatorResult = 'ValidatorResult';
        $ticketValidatorMock = $this->container->get('ticketValidator');
        $ticketValidatorMock
            ->method('validate')
            ->willReturn($validatorResult);

        $pageMock = $this->container->get('page');
        $pageMock
            ->expects($this->once())
            ->method('getTicketValidatorResultPage')
            ->with($this->equalTo($validatorResult));
        $action($request, $response, [ 'key' => $key, 'eventId' => $eventId, 'code' => $code ]);
    }
}