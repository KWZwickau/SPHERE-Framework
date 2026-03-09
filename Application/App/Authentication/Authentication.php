<?php

namespace SPHERE\Application\App\Authentication;

use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Authentication\Process\SignIn;
use SPHERE\Application\App\Authentication\Process\SignOut;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Link\Identifier;
use Throwable;

/**
 *
 */
class Authentication implements ApplicationInterface
{
    // 60 * 60 * 24 * 120 => 120 Days
    public const AUTHENTICATION_TOKEN_TIMEOUT = 60 * 60 * 24 * 120;
    // 60 * 60 / 2 => 1/2 Hour
    public const ACCESS_TOKEN_TIMEOUT = 60 * 60 / 2;
    // 60 * 2 => 2 Minutes
    public const OTP_TOKEN_TIMEOUT = 60 * 2;

    public static function registerApplication(): void
    {
        SignIn::registerModule();
        SignOut::registerModule();
    }

    public static function produceAuthenticationToken(): string
    {
        return hash('sha512', uniqid(__METHOD__, true));
    }

    public static function produceOtpToken(): ?string
    {
        try {
            return str_pad((string)random_int(10, 900), 3, "0", STR_PAD_LEFT)
                . '-'
                . str_pad((string)random_int(10, 900), 3, "0", STR_PAD_LEFT);
        } catch (Throwable $exception) {
            return null;
        }
    }

    /**
     * @param string $deviceIdentifier
     * @param string $accessToken
     *
     * @return bool|null null if access token or authentication token invalid
     */
    public static function hasSession(string $deviceIdentifier, string $accessToken): ?bool
    {
        // Token valid / not timed out?
        $tblDevice = self::useService()->getDeviceByAccess($accessToken);
        if (!$tblDevice) {
            return null;
        }
        // Device valid?
        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return null;
        }
        // Authentication token not timed out?
        if(!$tblDevice->getAuthenticationToken()) {
            return null;
        }
        // Account Service handles session timeout internally
        $tblAccount = Account::useService()->getAccountBySession($accessToken);
        if (!$tblAccount) {
            return false;
        }
        // Connect app session to ssw session
        session_destroy();
        session_id($accessToken);
        session_start();
        session_write_close();
        return true;
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Process/Service/Entity', __NAMESPACE__ . '\Process\Service\Entity'
        );
    }

    public static function createSession(string $deviceIdentifier, string $accessToken): ?bool
    {
        // Token valid / not timed out?
        $tblDevice = self::useService()->getDeviceByAccess($accessToken);
        if (!$tblDevice) {
            return null;
        }
        // Device valid?
        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return null;
        }
        // Authentication token not timed out?
        if(!$tblDevice->getAuthenticationToken()) {
            return null;
        }

        // Timeout in seconds from now
        Account::useService()->createSession(
            $tblDevice->getServiceTblAccount(), $accessToken, $tblDevice->getAccessTimeout() - time()
        );

        // Re-/Check session
        return self::hasSession($deviceIdentifier, $accessToken);
    }

    public static function produceAccessToken(): string
    {
        return hash('sha256', uniqid(__METHOD__, true));
    }
}
