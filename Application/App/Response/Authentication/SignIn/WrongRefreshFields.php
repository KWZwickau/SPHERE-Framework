<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response401;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class WrongRefreshFields extends Response401
{
    public function __construct()
    {
        $content = [
            'url' => '/app/authentication/process/refresh',
            'method' => Request::METHOD_POST,
            'provide' => [
                'deviceIdentifier' => [
                    'type' => 'string'
                ],
                'authenticationToken' => [
                    'type' => 'string'
                ]
            ]
        ];

        parent::__construct($content);
    }

}
