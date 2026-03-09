<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response400;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MissingRefreshFields extends Response400
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
