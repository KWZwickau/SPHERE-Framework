<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response405;

/**
 *
 */
class RequestMethod extends Response405
{
    public function __construct()
    {
        parent::__construct($_SERVER['REQUEST_METHOD']);
    }

    public static function wasPostMethod(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public static function wasWrong(): Response405
    {
        return new self();
    }
}
