<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * Indicates that the request is not available anymore
 *
 * AbstractResponse::HTTP_GONE
 */
class Response410 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_GONE, $context);
    }
}
