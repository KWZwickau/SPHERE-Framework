<?php

namespace SPHERE\Application\App\Response;


/**
 *
 */
abstract class AbstractSuccessResponse extends AbstractResponse implements SuccessInterface
{
    public function __construct(mixed $content, int $code, $headers = [])
    {
        $content = [
            'code' => $code,
            'title' => AbstractResponse::$statusTexts[$code] ?? 'Unknown',
            'content' => $content
        ];
        parent::__construct($content, $code, $headers);
    }
}
