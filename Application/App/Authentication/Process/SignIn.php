<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response307;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response403;
use SPHERE\Application\App\Response\Code\Response405;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\Code\Response500;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 *
 */
class SignIn implements ModuleInterface
{
    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/sign-in', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
    }

    public static function handleRequest(
        ?string $credentialIdentifier = null,
        ?string $deviceIdentifier = null,
        ?string $processToken = null,
    ): ResponseInterface {
        // ---
        // Validate request input
        // ---
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new Response405($_SERVER['REQUEST_METHOD']);
        }

        // ---
        // Validate user input
        // ---
        // Test availability (structure)
        if (
            null === $deviceIdentifier
            || null === $credentialIdentifier
        ) {
            return new Response400('Credentials not provided');
        }
        // Test compatibility (content)
        if (
            empty($deviceIdentifier)
            || empty($credentialIdentifier)
        ) {
            return new Response422('Credentials not provided');
        }

        // ---
        // Validate process token
        // ---
        if (empty($processToken)) {
            // Generate new process token
            $processToken = hash('sha256', uniqid('processToken', true));
            // Find/Create device and bind process token with device
            $tblDevice = Authentication::useService()->getDeviceWithIdentifierAndToken(
                $deviceIdentifier, $processToken
            );
            if (!$tblDevice) {
                return new Response403('Credentials not valid');
            }
            return new Response307(
                '/app/authentication/process/sign-in?processToken=' . $processToken . '#' . __LINE__
            );
        }

        // ---
        // Find current target
        // ---
        // Find Account
        $tblAccount = Account::useService()->getAccountByUsername($credentialIdentifier);
        if (!$tblAccount) {
            return new Response403('Credentials not valid');
        }
        // Find device
        $tblDevice = Authentication::useService()->getDeviceByIdentifier($deviceIdentifier);
        if (!$tblDevice) {
            return new Response403('Credentials not valid');
        }
        // Validate process
        if (
            null === $tblDevice->getProcessToken()
            || null === $tblDevice->getProcessTimeout()
            || $tblDevice->getProcessToken() !== $processToken
            || $tblDevice->getProcessTimeout() < time()
        ) {
            // New process startet or timed out? -> Reset sign-in process
            Authentication::useService()->resetAllSteps($tblAccount, $tblDevice);
            // Try again
            return new Response307('/app/authentication/process/sign-in#' . __LINE__);
        }



        /*

        // Find or create token entity
        try {
            $tblToken = self::useService()->getToken($tblAccount, $deviceToken);
        } catch (Throwable $exception) {
            return new Response500($exception->getMessage(), $exception->getTrace());
        }

        // If no process token is given or matched then re-/run everything (except credentials)
        if (!$tblToken || $processToken !== $tblToken->getProcessToken()) {

            try {
                $tblToken = self::useService()->createToken($tblAccount, $deviceToken, $processToken);
                if (!$tblToken) {
                    return new Response502('Sign-In not accessible');
                }
            } catch (Throwable $exception) {
                return new Response500($exception->getMessage(), $exception->getTrace());
            }

            // Reset all solved process (except credentials)
            try {
                $tblFactorCredential = self::useService()->getFactorByName('Credentials');
                if (!$tblFactorCredential) {
                    return new Response502('Factors not accessible');
                }
            } catch (Throwable $exception) {
                return new Response500($exception->getMessage(), $exception->getTrace());
            }
            try {
                $tblProcesses = self::useService()->getProcessesByToken($tblToken, true);
            } catch (Throwable $exception) {
                return new Response500($exception->getMessage(), $exception->getTrace());
            }
            if (!empty($tblProcesses)) {
                foreach ($tblProcesses as $tblProcess) {
                    try {
                        $tblFactorProcess = $tblProcess->getTblFactor();
                        if (!$tblFactorProcess) {
                            return new Response502('Factors not accessible');
                        }
                        if ($tblFactorProcess->getId() !== $tblFactorCredential->getId()) {
                            self::useService()->updateProcessSolved($tblProcess, false);
                        }
                    } catch (Throwable $exception) {
                        return new Response500($exception->getMessage(), $exception->getTrace());
                    }
                }
            }

            return new Response401('New process started',
                '/app/authentication/process/sign-in?processToken=' . $processToken . '#' . __LINE__
            );
        }

        // ---
        // Validate current process against identification requirements
        // ---

        // Validate process list integrity
        try {
            // Select all current processes
            $tblProcesses = self::useService()->getProcessesByToken($tblToken);
        } catch (Throwable $exception) {
            return new Response500($exception->getMessage(), $exception->getTrace());
        }
        // Find account identification factors (steps)
        $tblAuthentications = Account::useService()->getAuthenticationListByAccount($tblAccount);
        if (!$tblAuthentications) {
            return new Response502('Authentication not accessible');
        }
        try {
            $tblSteps = [];
            foreach ($tblAuthentications as $tblAuthentication) {
                $tblStepList = self::useService()->getStepsByIdentification(
                    $tblAuthentication->getTblIdentification()
                );
                if (null === $tblStepList) {
                    // This identification has no mfa steps ô.O
                    continue;
                }
                $tblSteps += $tblStepList;
            }
            $tblSteps = array_unique($tblSteps);
        } catch (Throwable $exception) {
            return new Response500($exception->getMessage(), $exception->getTrace());
        }
        // If new process list, then fill with current identification requirement
        if (empty($tblProcesses)) {
            foreach ($tblSteps as $tblStep) {
                try {
                    self::useService()->createProcess($tblToken, $tblStep->getTblFactor());
                } catch (Throwable $exception) {
                    return new Response500($exception->getMessage(), $exception->getTrace());
                }
            }
        } else {
            // If not empty, then validate current process against required steps
            foreach ($tblSteps as $tblStep) {
                try {
                    $tblFactorStep = $tblStep->getTblFactor();
                    if (null === $tblFactorStep) {
                        return new Response502('Factors not accessible');
                    }
                } catch (Throwable $exception) {
                    return new Response500($exception->getMessage(), $exception->getTrace());
                }
                // Continue step if any process has a matching factor
                foreach ($tblProcesses as $tblProcess) {
                    try {
                        $tblFactorProcess = $tblProcess->getTblFactor();
                        if (null === $tblFactorProcess) {
                            return new Response502('Factors not accessible');
                        }
                    } catch (Throwable $exception) {
                        return new Response500($exception->getMessage(), $exception->getTrace());
                    }
                    if ($tblFactorProcess->getId() === $tblFactorStep->getId()) {
                        continue 2;
                    }
                }
                // Step factor not satisfied by process, clear current process and start over
                foreach ($tblProcesses as $tblProcess) {
                    try {
                        self::useService()->destroyProcess($tblProcess);
                    } catch (Throwable $exception) {
                        return new Response500($exception->getMessage(), $exception->getTrace());
                    }
                }
                // Retry
                return new Response307(
                    '/app/authentication/process/sign-in?processToken=' . $processToken . '#' . __LINE__
                );
            }
        }

        // ---
        // Find current challenge
        // ---

//        return new Response501((new DebuggerFactory())->createLogger(new QueryLogger())->getLog());

        // If process token provided point to next unsolved factor
        try {
            $tblProcesses = self::useService()->getProcessesByToken($tblToken, false);
            if (!empty($tblProcesses)) {
                $tblProcess = current($tblProcesses);
                $tblFactor = $tblProcess->getTblFactor();
                if (!$tblFactor) {
                    return new Response502('Factors not accessible');
                }
                // TODO: Move switch and information into db to TblFactor
                switch ($tblFactor->getName()) {
                    case 'Credentials':
                        return new Response401([
                            'url' => '/app/authentication/factor/credentials?processToken=' . $processToken . '#' . __LINE__,
                            'prompt' => [
                                'credentialIdentifier' => [
                                    'label' => 'Benutzername',
                                    'type' => 'string'
                                ],
                                'credentialPassword' => [
                                    'label' => 'Passwort',
                                    'type' => 'string',
                                    'sensitive' => true
                                ],
                            ]
                        ]);
                    case 'Yubikey':
                        return new Response401([
                            'url' => '/app/authentication/factor/yubikey?processToken=' . $processToken . '#' . __LINE__,
                            'prompt' => [
                                'credentialToken' => [
                                    'label' => 'YubiKey',
                                    'type' => 'string',
                                    'sensitive' => true
                                ],
                            ]
                        ]);
                    default:
                        return new Response501('Factor not implemented', $tblFactor->getName());
                }
            }
        } catch (Throwable $exception) {
            return new Response500($exception->getMessage(), $exception->getTrace());
        }

//        self::useService()->updateAuthenticationToken($tblToken);
//        self::useService()->updateAuthenticationTimeout($tblToken);
*/
        return new Response500('Authentication not accessible');
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }
}
