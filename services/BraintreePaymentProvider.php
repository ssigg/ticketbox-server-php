<?php

namespace Services;

interface BraintreePaymentProviderInterface {
    function getToken();
    function sale($amount, $nonce);
}

class BraintreePaymentProvider implements BraintreePaymentProviderInterface {
    private $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger, $settings) {
        $this->logger = $logger;
        \Braintree\Configuration::environment($settings['environment']);
        \Braintree\Configuration::merchantId($settings['merchantId']);
        \Braintree\Configuration::publicKey($settings['publicKey']);
        \Braintree\Configuration::privateKey($settings['privateKey']);
    }

    public function getToken() {
        $token = \Braintree\ClientToken::generate();
        return $token;
    }

    public function sale($amount, $nonce) {
        $transaction = [
            'amount' => $amount,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'submitForSettlement' => true
            ]
        ];
        $result = \Braintree\Transaction::sale($transaction);
        if (!$result->success) {
            $this->logger->warning($result);
        }
        return $result;
    }
}