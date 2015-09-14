<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Subject
 *
 * @package SPHERE\Application\Education\Lesson\Subject
 */
class Subject implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Fächer'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if ($tblSubjectAll) {
            /** @var TblSubject $tblSubject */
            foreach ((array)$tblSubjectAll as $Index => $tblSubject) {
                $tblSubjectAll[$tblSubject->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            $tblSubject->getAcronym()
                            , array(3, 3, 3, 3)
                        ),
                        new LayoutColumn(
                            $tblSubject->getName()
                            .new Muted(new Small('<br/>'.$tblSubject->getDescription()))
                            , array(9, 9, 9, 9)
                        ),
                    ))));
                $tblSubjectAll[$Index] = false;
            }
            $tblSubjectAll = array_filter($tblSubjectAll);
            Main::getDispatcher()->registerWidget('Fächer', new Panel('Fächer verfügbar', $tblSubjectAll), 3, 8);
        }

        Main::getDispatcher()->registerWidget('Fächer',
            new Panel('Anzahl an Fächern', 'Insgesamt: '.Subject::useService()->countSubjectAll()), 2, 1
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'Lesson', 'Subject', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Fächer');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Fächer'));

        return $Stage;
    }
}
