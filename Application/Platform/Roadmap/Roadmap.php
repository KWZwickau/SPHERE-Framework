<?php
namespace SPHERE\Application\Platform\Roadmap;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Roadmap\Youtrack\Credentials;
use SPHERE\Application\Platform\Roadmap\Youtrack\Issue;
use SPHERE\Application\Platform\Roadmap\Youtrack\Parser;
use SPHERE\Application\Platform\Roadmap\Youtrack\Sprint;
use SPHERE\Application\Platform\Roadmap\Youtrack\Utility;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Roadmap extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Roadmap'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    public function frontendDashboard()
    {

        $Stage = new Stage('KREDA', 'Roadmap');

        $Parser = new Parser(
            new Credentials(),
            'Typ: Feature,Bug Teilsystem: {10*},{03*},{2*} Status: Erfasst,Offen,{In Bearbeitung},Behoben'
        );
        $Map = $Parser->getMap();
        $Sprints = $Map->getSprints();

        $LayoutColumns = array();
        /** @var Sprint $Sprint */
        foreach ((array)$Sprints as $Sprint) {

            $IssueList = array();
            $Issues = $Sprint->getIssues();
            /** @var Issue $Issue */
            foreach ((array)$Issues as $Issue) {
                $IssueList[] = new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn($Issue->getPriority(),1),
                    new LayoutColumn($Issue->getState(),1),
                    new LayoutColumn($Issue->getType(),1),
                    new LayoutColumn($Issue->getId(),1),
                    new LayoutColumn($Issue->getSubsystem(),1),
                    new LayoutColumn($Issue->getTitle(),7),
                ))));
            }
            $LayoutColumns[] = new LayoutColumn(
                new Panel(
                    $Sprint->getVersion().' # '.$Sprint->getTimestampStart().' - '.$Sprint->getTimestampFinish(),
                    $IssueList
                )
            );
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow($LayoutColumns))));

        return $Stage;
    }
}
