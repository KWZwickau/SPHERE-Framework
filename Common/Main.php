<?php
namespace SPHERE\Common;

use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use MOC\V\Component\Router\Component\Bridge\Repository\UniversalRouter;
use SPHERE\Application\Billing\Billing;
use SPHERE\Application\Company\Company;
use SPHERE\Application\Dispatcher;
use SPHERE\Application\Education\Education;
use SPHERE\Application\People\People;
use SPHERE\Application\Platform\Platform;
use SPHERE\Application\Platform\System;
use SPHERE\Application\Transfer\Transfer;
use SPHERE\Common\Window\Display;
use SPHERE\Common\Window\Error;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Authenticator\Authenticator;
use SPHERE\System\Authenticator\Type\Get;
use SPHERE\System\Authenticator\Type\Post;
use SPHERE\System\Extension\Extension;

/**
 * Class Main
 *
 * @package SPHERE\Common
 */
class Main extends Extension
{

    /** @var Display $Display */
    private static $Display = null;
    /** @var Dispatcher $Dispatcher */
    private static $Dispatcher = null;

    /**
     *
     */
    public function __construct()
    {

        if (self::getDisplay() === null) {
            self::$Display = new Display();
        }
        if (self::getDispatcher() === null) {
            self::$Dispatcher = new Dispatcher( new UniversalRouter() );
        }
    }

    /**
     * @return Display
     */
    public static function getDisplay()
    {

        return self::$Display;
    }

    /**
     * @return Dispatcher
     */
    public static function getDispatcher()
    {

        return self::$Dispatcher;
    }

    public function runPlatform()
    {

        try {
            $this->getDebugger();
            $this->setErrorHandler();
            $this->setShutdownHandler();

            /**
             * Register Cluster
             */
            Platform::registerCluster();
            People::registerCluster();
            Company::registerCluster();
            Education::registerCluster();
            Billing::registerCluster();
            Transfer::registerCluster();
            /**
             * Execute Request
             */
            if ($this->runAuthenticator()) {
                self::getDisplay()->setContent(
                    self::getDispatcher()->fetchRoute(
                        $this->getRequest()->getPathInfo()
                    )
                );
            }
        } catch( PDOException $Exception ) {
            $this->runSelfHeal( $Exception );
        } catch( TableNotFoundException $Exception ) {
            $this->runSelfHeal( $Exception );
        } catch( \PDOException $Exception ) {
            $this->runSelfHeal( $Exception );
        } catch( \ErrorException $Exception ) {
            self::getDisplay()->setException( $Exception, 'Error' );
        } catch( \Exception $Exception ) {
            self::getDisplay()->setException( $Exception, 'Exception' );
        }

        try {
            echo self::getDisplay()->getContent();
            exit(0);
        } catch( \Exception $Exception ) {
            $this->runSelfHeal( $Exception );
        }
    }

    /**
     *
     */
    private function setErrorHandler()
    {

        set_error_handler(
            function ( $Code, $Message, $File, $Line ) {

                if (!preg_match( '!apc_store.*?was.*?on.*?gc-list.*?for!is', $Message )) {
                    throw new \ErrorException( $Message, 0, $Code, $File, $Line );
                }
            }, E_ALL
        );
    }

    /**
     *
     */
    private function setShutdownHandler()
    {

        register_shutdown_function(
            function () {

                $Error = error_get_last();
                if (!$Error) {
                    return;
                }
                $Display = new Display();
                $Display->addServiceNavigation(
                    new Link( new Link\Route( '/' ), new Link\Name( 'Zurück zur Anwendung' ) )
                );
                $Display->setException(
                    new \ErrorException( $Error['message'], 0, $Error['type'], $Error['file'], $Error['line'] ),
                    'Shutdown'
                );
                echo $Display->getContent( true );
            }
        );
    }

    /**
     * @return bool
     */
    private function runAuthenticator()
    {

        if (!array_key_exists( 'REST', $this->getRequest()->getParameterArray() )) {
            $Get = ( new Authenticator( new Get() ) )->getAuthenticator();
            $Post = ( new Authenticator( new Post() ) )->getAuthenticator();
            if (!( $Get->validateSignature() && $Post->validateSignature() )) {
                self::getDisplay()->setClusterNavigation();
                self::getDisplay()->setApplicationNavigation();
                self::getDisplay()->setModuleNavigation();
                self::getDisplay()->setServiceNavigation( new Link(
                    new Link\Route( '/' ),
                    new Link\Name( 'Zurück zur Anwendung' )
                ) );
                self::getDisplay()->setContent( Dispatcher::fetchRoute( 'System/Assistance/Error/Authenticator' ) );
                return false;
            }
        }
        return true;
    }

    /**
     * @param \Exception $Exception
     */
    private function runSelfHeal( \Exception $Exception = null )
    {

        $Display = new Display();
        $Display->setContent(
            ( $Exception
                ? new Error( $Exception->getCode(), $Exception->getMessage() )
                : ''
            ).
            ( new System\Database\Database() )->frontendSetup( false )
            .( new Redirect( $this->getRequest()->getPathInfo(), 60 ) )
        );
        echo $Display->getContent( true );
        exit( 0 );
    }
}
