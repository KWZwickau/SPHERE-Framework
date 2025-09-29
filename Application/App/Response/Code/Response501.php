<?php

namespace SPHERE\Application\App\Response\Code;


/**
 * Things that should happen can't happen because they are not implemented yet
 *
 * AbstractResponse5xx::HTTP_NOT_IMPLEMENTED
 */
class Response501 extends AbstractResponse5xx
{
    public function __construct(mixed $content, mixed $context = null)
    {
        parent::__construct($content, self::HTTP_NOT_IMPLEMENTED, $context);
    }
}
