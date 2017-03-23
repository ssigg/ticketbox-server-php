<?php

class LogActionsTest extends \PHPUnit_Framework_TestCase {
    private $container;

    protected function setUp() {
        $this->container = new \Slim\Container;

        $logMock = $this->getMockBuilder(LogInterface::class)
            ->setMethods(['info', 'warning', 'error'])
            ->getMock();
        $this->container['log'] = $logMock;
    }

    public function testInfoLogsInfo() {
        $action = new Actions\LogClientMessageAction($this->container);
        $level = 'info';
        $message = 'message';
        $userData = 'userData';

        $data = [
            'level' => $level,
            'message' => $message,
            'userData' => $userData
        ];
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/log'
        ]);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody($data);
        $response = new \Slim\Http\Response();

        $logMock = $this->container->get('log');
        $logMock
            ->expects($this->once())
            ->method('info')
            ->with($this->equalTo($message . "\n\nUser data:\n" . print_r($userData, true)));
        $logMock->expects($this->never())->method('warning');
        $logMock->expects($this->never())->method('error');
        $action($request, $response, [ ]);
    }

    public function testWarningLogsWaning() {
        $action = new Actions\LogClientMessageAction($this->container);
        $level = 'warning';
        $message = 'message';
        $userData = 'userData';

        $data = [
            'level' => $level,
            'message' => $message,
            'userData' => $userData
        ];
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/log'
        ]);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody($data);
        $response = new \Slim\Http\Response();

        $logMock = $this->container->get('log');
        $logMock->expects($this->never())->method('info');
        $logMock
            ->expects($this->once())
            ->method('warning')
            ->with($this->equalTo($message . "\n\nUser data:\n" . print_r($userData, true)));
        $logMock->expects($this->never())->method('error');
        $action($request, $response, [ ]);
    }

    public function testErrorLogsError() {
        $action = new Actions\LogClientMessageAction($this->container);
        $level = 'error';
        $message = 'message';
        $userData = 'userData';

        $data = [
            'level' => $level,
            'message' => $message,
            'userData' => $userData
        ];
        
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/log'
        ]);
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody($data);
        $response = new \Slim\Http\Response();

        $logMock = $this->container->get('log');
        $logMock->expects($this->never())->method('info');
        $logMock->expects($this->never())->method('warning');
        $logMock
            ->expects($this->once())
            ->method('error')
            ->with($this->equalTo($message . "\n\nUser data:\n" . print_r($userData, true)));
        $action($request, $response, [ ]);
    }
}