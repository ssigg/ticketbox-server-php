<?php

namespace Services;

interface BraintreePaymentProviderInterface {
    function getToken();
    function sale($amount, $nonce);
}

class BraintreePaymentProvider implements BraintreePaymentProviderInterface {
    public function __construct($settings) {
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
        return $result;
    }
}