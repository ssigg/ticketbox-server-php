<?php

class TokenProviderTest extends \PHPUnit_Framework_TestCase {
    private $reservationMapperMock;
    private $uuidFactoryMock;

    protected function setUp() {
        $this->sessionMock = $this->getMockBuilder(\duncan3dc\Sessions\SessionInterface::class)
            ->setMethods(['get', 'set'])
            ->getMockForAbstractClass();

        $this->uuidFactoryMock = $this->getMockBuilder(\Ramsey\Uuid\UuidFactoryInterface::class)
            ->setMethods(['uuid1'])
            ->getMockForAbstractClass();
    }

    public function testProvideUsesSessionGet() {
        $tokenProvider = new Services\TokenProvider($this->sessionMock, $this->uuidFactoryMock);

        $this->sessionMock->expects($this->once())->method('get');
        $tokenProvider->provide();
    }

    public function testProvideUsesValueFromSessionWhenAvailable() {
        $this->sessionMock
            ->method('get')
            ->willReturn('token');
        $tokenProvider = new Services\TokenProvider($this->sessionMock, $this->uuidFactoryMock);

        $token = $tokenProvider->provide();
        $this->assertSame('token', $token);
    }

    public function testProvideGetsValueFromSession() {
        $this->sessionMock
            ->method('get')
            ->willReturn('token');
        $tokenProvider = new Services\TokenProvider($this->sessionMock, $this->uuidFactoryMock);

        $token = $tokenProvider->provide();
        $this->assertSame('token', $token);
    }

    public function testProvideDoesNotSetSessionValueWhenValueIsAvailable() {
        $this->sessionMock
            ->method('get')
            ->willReturn('token');
        $tokenProvider = new Services\TokenProvider($this->sessionMock, $this->uuidFactoryMock);

        $this->sessionMock->expects($this->never())->method('set');
        $tokenProvider->provide();
    }

    public function testProvideSetsSessionValueWhenValueIsNotAvailable() {
        $this->sessionMock
            ->method('get')
            ->willReturn(null);
        $tokenProvider = new Services\TokenProvider($this->sessionMock, $this->uuidFactoryMock);

        $this->sessionMock->expects($this->once())->method('set');
        $tokenProvider->provide();
    }
}