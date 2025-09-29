<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;

/**
 * The user input seems not right in some way (request content)
 *
 * AbstractResponse::HTTP_UNPROCESSABLE_ENTITY
 */
class Response422 extends AbstractErrorResponse
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_UNPROCESSABLE_ENTITY, $context);
    }
}
