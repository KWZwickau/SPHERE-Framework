<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractSuccessResponse;

/**
 * It worked!
 *
 * AbstractResponse::HTTP_OK
 */
class Response200 extends AbstractSuccessResponse
{
    public function __construct(mixed $content)
    {
        parent::__construct($content, self::HTTP_OK);
    }
}
