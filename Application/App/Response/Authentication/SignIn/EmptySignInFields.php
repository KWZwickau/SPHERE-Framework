<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response422;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class EmptySignInFields extends Response422
{
    public function __construct()
    {
        $content = [
            'behaviour' => 'sign-in',
            'url' => '/app/authentication/process/sign-in',
            'method' => Request::METHOD_POST,
            'provide' => [
                'deviceIdentifier' => [
                    'type' => 'string'
                ]
            ],
            'prompt' => [
                'credentialIdentifier' => [
                    'label' => 'Benutzername',
                    'type' => 'string'
                ],
                'credentialPassword' => [
                    'label' => 'Passwort',
                    'type' => 'string',
                    'sensitive' => true
                ]
            ]
        ];

        parent::__construct($content);
    }

}
