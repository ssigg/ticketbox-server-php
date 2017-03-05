<?php

namespace Actions;

use Interop\Container\ContainerInterface;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ValidateTicketAction {
    private $ticketValidator;
    private $ticketTestValidator;
    private $page;

    public function __construct(ContainerInterface $container) {
        $this->ticketValidator = $container->get('ticketValidator');
        $this->ticketTestValidator = $container->get('ticketTestValidator');
        $this->page = $container->get('page');
    }

    public function __invoke(Request $request, Response $response, $args = []) {
        $key = $args['key'];
        $eventId = $args['eventId'];
        $code = $args['code'];

        $body = $response->getBody();
        $ticketValidatorResult = null;
        if (in_array($code, [ 'ok', 'error', 'warning' ])) {
            $ticketValidatorResult = $this->ticketTestValidator->validate($key, $eventId, $code);
        } else {
            $ticketValidatorResult = $this->ticketValidator->validate($key, $eventId, $code);
        }
        $pageContent = $this->page->getTicketValidatorResultPage($ticketValidatorResult);
        $body->write($pageContent);
        return $response;
    }
}