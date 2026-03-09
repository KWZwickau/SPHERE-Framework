<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response422;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class EmptyRefreshFields extends Response422
{
    public function __construct()
    {
        $content = [
            'behaviour' => 'refresh',
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
