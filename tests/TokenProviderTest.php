<?php

class TokenProviderTest extends \PHPUnit_Framework_TestCase {
    private $reservationMapperMock;

    protected function setUp() {
        $this->sessionMock = $this->getMockBuilder(\duncan3dc\Sessions\SessionInterface::class)
            ->setMethods(['get', 'set'])
            ->getMockForAbstractClass();
    }

    public function testProvideUsesSessionGet() {
        $tokenProvider = new Services\TokenProvider($this->sessionMock);

        $this->sessionMock->expects($this->once())->method('get');
        $tokenProvider->provide();
    }

    public function testProvideUsesValueFromSessionWhenAvailable() {
        $this->sessionMock
            ->method('get')
            ->willReturn('token');
        $tokenProvider = new Services\TokenProvider($this->sessionMock);

        $token = $tokenProvider->provide();
        $this->assertSame('token', $token);
    }

    public function testProvideGetsValueFromSession() {
        $this->sessionMock
            ->method('get')
            ->willReturn('token');
        $tokenProvider = new Services\TokenProvider($this->sessionMock);

        $token = $tokenProvider->provide();
        $this->assertSame('token', $token);
    }

    public function testProvideDoesNotSetSessionValueWhenValueIsAvailable() {
        $this->sessionMock
            ->method('get')
            ->willReturn('token');
        $tokenProvider = new Services\TokenProvider($this->sessionMock);

        $this->sessionMock->expects($this->never())->method('set');
        $tokenProvider->provide();
    }

    public function testProvideSetsSessionValueWhenValueIsNotAvailable() {
        $this->sessionMock
            ->method('get')
            ->willReturn(null);
        $tokenProvider = new Services\TokenProvider($this->sessionMock);

        $this->sessionMock->expects($this->once())->method('set');
        $tokenProvider->provide();
    }
}