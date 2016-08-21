<?php

namespace Services;

interface TokenProviderInterface {
    function provide();
}

class TokenProvider implements TokenProviderInterface {
    private $session;

    public function __construct(\duncan3dc\Sessions\SessionInterface $session) {
        $this->session = $session;
    }

    public function provide() {
        $token = $this->session->get('token');
        if ($token == null) {
            $token = bin2hex(openssl_random_pseudo_bytes(8));
            $this->session->set('token', $token);
        }
        return $token;
    }
}