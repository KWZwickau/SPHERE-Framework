<?php
namespace SPHERE\Application\Education\School\Course;

use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Course
 *
 * @package SPHERE\Application\Education\School\Course
 */
class Course implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Bildungsgang'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        $tblCourseAll = self::useService()->getCourseAll();
        if ($tblCourseAll) {
            /** @var TblCourse $tblCourse */
            foreach ((array)$tblCourseAll as $Index => $tblCourse) {
                $tblCourseAll[$tblCourse->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            $tblCourse->getName()
                            .new Muted(new Small('<br/>'.$tblCourse->getDescription()))
                        ),
                    ))));
                $tblCourseAll[$Index] = false;
            }
            $tblCourseAll = array_filter($tblCourseAll);
            Main::getDispatcher()->registerWidget('School-Course', new Panel('Bildungsgänge verfügbar', $tblCourseAll),
                3, 3);
        }
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'School', 'Course', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Bildungsgang');

        $Stage->setMessage(
            new Warning('Bildungsgänge sind im Moment fest hinterlegt')
        );

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('School-Course'));

        return $Stage;
    }
}
