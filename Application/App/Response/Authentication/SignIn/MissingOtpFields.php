<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response400;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MissingOtpFields extends Response400
{
    public function __construct(int $timeout)
    {
        $content = [
            'behaviour' => 'otp',
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
