<?php
namespace SPHERE\Common;

use MOC\V\Component\Router\Component\Bridge\Repository\UniversalRouter;
use SPHERE\Common\Window\Display;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Repository\Debugger;

class Main
{

    /** @var Debugger $Debugger */
    private $Debugger = null;
    /** @var Display $Display */
    private $Display = null;
    /** @var UniversalRouter $Router */
    private $Router = null;

    public function __construct()
    {

        $this->Debugger = new Debugger();
        $this->Display = new Display();
        $this->Router = new UniversalRouter();

        $this->setErrorHandler();
        $this->setShutdownHandler();
    }

    public function setErrorHandler()
    {

        set_error_handler(
            function ( $Code, $Message, $File, $Line ) {

                if (!preg_match( '!apc_store.*?was.*?on.*?gc-list.*?for!is', $Message )) {
                    throw new \ErrorException( $Message, 0, $Code, $File, $Line );
                }
            }, E_ALL
        );
    }

    public function setShutdownHandler()
    {

        register_shutdown_function(
            function ( Display $Display ) {

                $Error = error_get_last();
                if (!$Error) {
                    return;
                }
                $Display->addServiceNavigation(
                    new Link( new Link\Route( '/' ), new Link\Name( 'Zurück zur Anwendung' ) )
                );
                print $Display->getContent();
            }, $this->Display
        );
    }

    /**
     * @return Display
     */
    public function getDisplay()
    {

        return $this->Display;
    }
}
