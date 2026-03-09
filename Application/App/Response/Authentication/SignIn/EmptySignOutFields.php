<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response422;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class EmptySignOutFields extends Response422
{
    public function __construct()
    {
        $content = [
            'behaviour' => 'sign-out',
            'url' => '/app/authentication/process/sign-out',
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
