<?php

namespace Guzzle\Tests\Mock;

use Guzzle\Http\Message\Response;
use Guzzle\Plugin\ErrorResponse\ErrorResponseExceptionInterface;
use Guzzle\Service\Command\CommandInterface;

class ErrorResponseMock extends \Exception implements ErrorResponseExceptionInterface
{

    public $command;
    public $response;

    public function __construct( $command, $response )
    {

        $this->command = $command;
        $this->response = $response;
        $this->message = 'Error from '.$response;
    }

    public static function fromCommand( CommandInterface $command, Response $response )
    {

        return new self( $command, $response );
    }
}
