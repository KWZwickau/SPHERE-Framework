<?php

namespace SPHERE\Application\Document\Standard\KamenzReport;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class KamenzReport
 * @package SPHERE\Application\Document\Standard\KamenzReport
 */
class KamenzReport extends AbstractModule implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kamenz-Statistik'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendShowKamenz'
        ));
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

    /**
     * @return Stage
     */
    public static function frontendShowKamenz()
    {

        $Stage = new Stage('Kamenz-Statistik', 'Bereit zum Download');

        $Stage->addButton(new External('Herunterladen: Grundschulstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReportGS\Create',
            new Download(), array(), 'Kamenz-Statistik der GS herunterladen'));

        $Stage->addbutton(new External('Herunterladen: Oberschulstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(), array(), 'Kamenz-Statistik Herungerladen'));

        $Stage->addButton(new External('Herunterladen: Gymnasialstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReportGym\Create',
            new Download(), array(), 'Kamenz-Statistik des Gymnasiums herunterladen'));

//        Debugger::screenDump(KamenzReportService::setKamenzReportContent(array()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Success('Hier können Sie die Kamenz-Statistiken herunterladen')
                        )
                    )
                )
            )
        );
        return $Stage;
    }
}