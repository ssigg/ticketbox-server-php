<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
class LogClientMessageAction {
    private $log;

    public function __construct(ContainerInterface $container) {
        $this->log = $container->get('log');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $data = $request->getParsedBody();
        $level = $data['severity'];
        $message = $data['message'];
        $userData = $data['userData'];
        $logMessage = $message . "\n\nUser data:\n" . print_r($userData, true);

        if ($level == 'info') {
            $this->log->info($logMessage);
        } else if ($level == 'warning') {
            $this->log->warning($logMessage);
        } else if ($level == 'error') {
            $this->log->error($logMessage);
        }

        return $response->withStatus(201);
    }
}