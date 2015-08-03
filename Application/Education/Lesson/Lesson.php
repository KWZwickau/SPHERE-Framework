<?php
namespace SPHERE\Application\Education\Lesson;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Lesson implements IApplicationInterface
{

    public static function registerApplication()
    {

        Subject::registerModule();
        Division::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Unterricht' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ) );
    }

    public function frontendDashboard()
    {
        $Stage = new Stage( 'Dashboard', 'Unterricht' );

        return $Stage;
    }

}
