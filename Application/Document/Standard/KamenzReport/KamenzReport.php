<?php

namespace SPHERE\Application\Document\Standard\KamenzReport;

use SPHERE\Application\Education\School\Type\Type;
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
use SPHERE\Common\Frontend\Link\Repository\Standard;
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

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/SecondarySchool', __CLASS__.'::frontendValidateSecondarySchool'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {


    }

    /**
     * @return Stage
     */
    public static function frontendShowKamenz()
    {

        $Stage = new Stage('Kamenz-Statistik', 'Auswählen');

        $Stage->addButton(new Standard(
           'Oberschule / Mittelschule', '/Document/Standard/KamenzReport/Validate/SecondarySchool'
        ));

//        $Stage->addButton(new External('Herunterladen: Grundschulstatistik',
//            'SPHERE\Application\Api\Document\Standard\KamenzReportGS\Create',
//            new Download(), array(), 'Kamenz-Statistik der GS herunterladen'));



//        $Stage->addButton(new External('Herunterladen: Gymnasialstatistik',
//            'SPHERE\Application\Api\Document\Standard\KamenzReportGym\Create',
//            new Download(), array(), 'Kamenz-Statistik des Gymnasiums herunterladen'));


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            'Bitte wählen Sie eine Schulart aus.'
                        )
                    )
                )
            )
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendValidateSecondarySchool()
    {

        $Stage = new Stage('Kamenz-Statistik', 'Oberschule / Mittelschule validieren');

        $Stage->addbutton(new External('Herunterladen: Oberschulstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(), array(), 'Kamenz-Statistik Herungerladen'));

        //        Debugger::screenDump(KamenzReportService::setKamenzReportContent(array()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            KamenzService::validate(Type::useService()->getTypeByName('Mittelschule / Oberschule'))
                        )
                    )
                )
            )
        );

        return $Stage;
    }
}