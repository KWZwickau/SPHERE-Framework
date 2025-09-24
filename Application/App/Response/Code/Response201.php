<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractSuccessResponse;

/**
 * AbstractResponse::HTTP_CREATED
 */
class Response201 extends AbstractSuccessResponse
{
    public function __construct(mixed $content)
    {
        parent::__construct($content, self::HTTP_CREATED);
    }
}
