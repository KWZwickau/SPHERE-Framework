<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response400;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MissingSignOutFields extends Response400
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
