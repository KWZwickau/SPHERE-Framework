<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * Indicates that the request could not be processed because of conflict in the current state of the resource
 *
 * AbstractResponse::HTTP_CONFLICT
 */
class Response409 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_CONFLICT, $context);
    }
}
