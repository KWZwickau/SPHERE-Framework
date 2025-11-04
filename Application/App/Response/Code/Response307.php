<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractSuccessResponse;

/**
 * In this case, the request should be repeated with another URI
 *
 * AbstractResponse::HTTP_TEMPORARY_REDIRECT
 */
class Response307 extends AbstractSuccessResponse
{
    public function __construct(string $uri)
    {
        parent::__construct('', self::HTTP_TEMPORARY_REDIRECT, ['Location' => $uri]);
    }
}
