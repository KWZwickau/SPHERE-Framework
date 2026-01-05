<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response307;

/**
 *
 */
class RetryProcess extends Response307
{
    public function __construct(?string $processToken = null)
    {
        $url = '/app/authentication/process/sign-in' . ($processToken ? '?processToken=' . $processToken : '');

        parent::__construct($url);
    }

}
