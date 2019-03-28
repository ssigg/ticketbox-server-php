<?php

namespace Services;

interface TokenProviderInterface {
    function provide();
}

class TokenProvider implements TokenProviderInterface {
    private $session;
    private $uuidFactory;

    public function __construct(\duncan3dc\Sessions\SessionInterface $session, \Ramsey\Uuid\UuidFactoryInterface $uuidFactory) {
        $this->session = $session;
        $this->uuidFactory = $uuidFactory;
    }

    public function provide() {
        $token = $this->session->get('token');
        if ($token == null) {
            $token = $this->uuidFactory->uuid1();
            $this->session->set('token', $token);
        }
        return $token;
    }
}