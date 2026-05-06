<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractSuccessResponse;

/**
 * It's ok
 *
 * AbstractResponse::HTTP_NO_CONTENT
 */
class Response204 extends AbstractSuccessResponse
{
    public function __construct()
    {
        parent::__construct(null, self::HTTP_NO_CONTENT);
    }
}
