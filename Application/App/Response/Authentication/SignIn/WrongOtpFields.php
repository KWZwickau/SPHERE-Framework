<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response401;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class WrongOtpFields extends Response401
{
    public function __construct(int $timeout)
    {
        $content = [
            'url' => '/app/authentication/process/sign-in',
            'method' => Request::METHOD_POST,
            'provide' => [
                'deviceIdentifier' => [
                    'type' => 'string'
                ],
                'credentialIdentifier' => [
                    'type' => 'string'
                ],
                'credentialPassword' => [
                    'type' => 'string'
                ]
            ],
            'prompt' => [
                'deviceCode' => [
                    'label' => 'Code',
                    'type' => 'string',
                    'timeout' => $timeout
                ],
            ]
        ];

        parent::__construct($content);
    }

}
