<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response422;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class EmptyCredentialFields extends Response422
{
    public function __construct(?string $processToken = null)
    {
        $content = [
            'url' => '/app/authentication/factor/credentials' . ($processToken ? '?processToken=' . $processToken : ''),
            'method' => Request::METHOD_POST,
            'provide' => [
                'deviceIdentifier' => [
                    'type' => 'string',
                    'sensitive' => true
                ],
                'credentialIdentifier' => [
                    'label' => 'Benutzername',
                    'type' => 'string'
                ]
            ],
            'prompt' => [
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
