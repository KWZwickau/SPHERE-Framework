<?php

namespace SPHERE\Application\App\Response\Code;


/**
 * The server cannot handle the request (because it is overloaded or down for maintenance)
 *
 * AbstractResponse5xx::HTTP_SERVICE_UNAVAILABLE
 */
class Response503 extends AbstractResponse5xx
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_SERVICE_UNAVAILABLE, $context);
    }
}
