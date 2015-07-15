<?php

namespace Guzzle\Tests\Mock;

use Guzzle\Service\Command\OperationCommand;
use Guzzle\Service\Command\ResponseClassInterface;

class CustomResponseModel implements ResponseClassInterface
{

    public $command;

    public function __construct( $command )
    {

        $this->command = $command;
    }

    public static function fromCommand( OperationCommand $command )
    {

        return new self( $command );
    }
}
