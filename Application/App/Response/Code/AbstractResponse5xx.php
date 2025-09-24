<?php

namespace SPHERE\Application\App\Response\Code;


use SPHERE\Application\App\Response\AbstractErrorResponse;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * 5xx base class
 */
class AbstractResponse5xx extends AbstractErrorResponse
{
    public function __construct(mixed $content, int $code, mixed $context = null)
    {
        // Prevent error debug information leakage
        if (!Debugger::isActive()) {
            $content = null;
        }

        parent::__construct($content, $code, $context);
    }
}
