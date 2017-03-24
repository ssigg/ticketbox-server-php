<?php

namespace Services;

interface BraintreePaymentProviderInterface {
    function getToken();
    function sale($amount, $nonce, $firstname, $lastname, $email);
}

class BraintreePaymentProvider implements BraintreePaymentProviderInterface {
    private $log;

    public function __construct(LogInterface $log, $settings) {
        $this->log = $log;
        \Braintree\Configuration::environment($settings['environment']);
        \Braintree\Configuration::merchantId($settings['merchantId']);
        \Braintree\Configuration::publicKey($settings['publicKey']);
        \Braintree\Configuration::privateKey($settings['privateKey']);
    }

    public function getToken() {
        $token = \Braintree\ClientToken::generate();
        return $token;
    }

    public function sale($amount, $nonce, $firstname, $lastname, $email) {
        $transaction = [
            'amount' => $amount,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'submitForSettlement' => true
            ],
            'customer' => [
                'firstName' => $firstname,
                'lastName' => $lastname,
                'email' => $email
            ]
        ];
        $result = \Braintree\Transaction::sale($transaction);
        if ($result->success) {
            $this->log->info('Recieved payment of ' . $amount . ' CHF.');
        } else {
            $this->log->warning($result);
        }
        return $result;
    }
}