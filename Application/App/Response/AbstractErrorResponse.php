<?php

namespace SPHERE\Application\App\Response;


use SPHERE\System\Extension\Repository\Debugger;

/**
 *
 */
abstract class AbstractErrorResponse extends AbstractResponse implements ErrorInterface
{
    public function __construct(mixed $content, int $code, mixed $context = null)
    {
        // Prevent error debug information leakage
        if (!Debugger::isActive()) {
            $context = null;
        }

        $content = [
            'code' => $code,
            'title' => AbstractResponse::$statusTexts[$code] ?? 'Unknown error',
            'error' => $content,
            'context' => $context
        ];

        parent::__construct($content, $code);
    }
}
