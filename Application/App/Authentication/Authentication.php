<?php

namespace SPHERE\Application\App\Authentication;

use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Authentication\Information\Menu;
use SPHERE\Application\App\Authentication\Information\Person;
use SPHERE\Application\App\Authentication\Process\Refresh;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\App\Authentication\Process\SignIn;
use SPHERE\Application\App\Authentication\Process\SignOut;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Link\Identifier;

/**
 *
 */
class Authentication implements ApplicationInterface
{
    // 60 * 60 * 24 * 30 => 30 Days
    // get new authentication token every ...
    public const AUTHENTICATION_TOKEN_REFRESH = 60 * 60 * 24 * 30;
    // 60 * 60 * 24 * 90 => 90 Days
    // maximum time to get a new authentication token, happens between refresh and timeout
    // 0d-fix-30d-new[30d-90d]-90d-logout
    public const AUTHENTICATION_TOKEN_TIMEOUT = 60 * 60 * 24 * 90;
    // 60 * 5 => 5 Minutes
    public const ACCESS_TOKEN_TIMEOUT = 60 * 5;

    public static function registerApplication(): void
    {
        SignIn::registerModule();
        Refresh::registerModule();
        SignOut::registerModule();

        Menu::registerModule();
        Person::registerModule();
    }

    public static function produceAuthenticationToken(): string
    {
        return hash('sha512', uniqid(__METHOD__, true));
    }

    public static function createSession(?string $deviceIdentifier, ?string $accessToken): ?bool
    {
        // Check parameter, token and device
        $tblDevice = self::validateDevice($deviceIdentifier, $accessToken);
        if (null === $tblDevice) {
            return false;
        }
        // Connect app session to ssw session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_id($accessToken);
        session_start();
        session_write_close();
        // Timeout in seconds from now
        Account::useService()->createSession(
            $tblDevice->getServiceTblAccount(), $accessToken, $tblDevice->getAccessTimeout() - time()
        );
        // Re-/Check session
        return self::hasSession($deviceIdentifier, $accessToken);
    }

    private static function validateDevice(?string $deviceIdentifier, ?string $accessToken): ?TblDevice
    {
        // Check parameter
        if (null === $deviceIdentifier) {
            return null;
        }
        if (null === $accessToken) {
            return null;
        }
        // Check valid access token (handles token timeout internally)
        $tblDevice = self::useService()->getDeviceByAccess($accessToken);
        if (!$tblDevice) {
            return null;
        }
        // Check valid authentication token (handles token timeout internally)
        $tblDevice = self::useService()->getDeviceByAuthentication($tblDevice->getAuthenticationToken());
        if (!$tblDevice) {
            return null;
        }
        // Check valid device
        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return null;
        }
        // Parameter, token and device are valid
        return $tblDevice;
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Process/Service/Entity', __NAMESPACE__ . '\Process\Service\Entity'
        );
    }

    public static function hasSession(?string $deviceIdentifier, ?string $accessToken): ?bool
    {
        // Check parameter, token and device
        $tblDevice = self::validateDevice($deviceIdentifier, $accessToken);
        if (null === $tblDevice) {
            return false;
        }
        // Check valid database session (handles session timeout internally)
        $tblAccount = Account::useService()->getAccountBySession($accessToken);
        if (!$tblAccount) {
            return false;
        }
        // Check valid php session
        if (session_id() !== $accessToken) {
            return false;
        }
        return true;
    }

    public static function produceAccessToken(): string
    {
        return hash('sha256', uniqid(__METHOD__, true));
    }
}
