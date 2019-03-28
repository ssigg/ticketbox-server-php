<?php

namespace Services;

abstract class TicketValidatorStatus {
    const Ok = 0;
    const Warning = 1;
    const Error = 2;
}

class TicketValidatorResult {
    public $messages;
    public $status;
    public function __construct($messages, $status) {
        $this->messages = $messages;
        $this->status = $status;
    }
}