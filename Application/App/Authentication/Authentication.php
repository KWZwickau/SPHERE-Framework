<?php
namespace SPHERE\Application\App\Authentication;

use SPHERE\Application\App\Authentication\Credentials\Credentials;
use SPHERE\Application\IApplicationInterface;

class Authentication implements IApplicationInterface
{
    public static function registerApplication(): void
    {
        Credentials::registerModule();
    }
}
