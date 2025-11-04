<?php

namespace SPHERE\Application\App\Response\Code;


/**
 * The server was acting as a gateway or proxy and received an invalid response from the upstream server
 *
 * AbstractResponse5xx::HTTP_BAD_GATEWAY
 */
class Response502 extends AbstractResponse5xx
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_BAD_GATEWAY, $context);
    }
}
