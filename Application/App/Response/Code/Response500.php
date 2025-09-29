<?php

namespace SPHERE\Application\App\Response\Code;


/**
 * Unhandled error but every error should be accounted for, think about a better solution ... -.0
 *
 * AbstractResponse5xx::HTTP_INTERNAL_SERVER_ERROR
 */
class Response500 extends AbstractResponse5xx
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_INTERNAL_SERVER_ERROR, $context);
    }
}
