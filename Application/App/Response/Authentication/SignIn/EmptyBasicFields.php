<?php

namespace SPHERE\Application\App\Response\Authentication\SignIn;

use SPHERE\Application\App\Response\Code\Response422;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class EmptyBasicFields extends Response422
{
    public function __construct(?string $processToken = null)
    {
        $content = [
            'url' => '/app/authentication/process/sign-in' . ($processToken ? '?processToken=' . $processToken : ''),
            'method' => Request::METHOD_POST,
            'provide' => [
                'deviceIdentifier' => [
                    'type' => 'string',
                    'sensitive' => true
                ]
            ],
            'prompt' => [
                'credentialIdentifier' => [
                    'label' => 'Benutzername',
                    'type' => 'string'
                ]
            ]
        ];

        parent::__construct($content);
    }

}
