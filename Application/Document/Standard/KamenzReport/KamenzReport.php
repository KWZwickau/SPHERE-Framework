<?php

namespace SPHERE\Application\Document\Standard\KamenzReport;

use SPHERE\Application\Document\Generator\Service\Kamenz\KamenzReportService;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;

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
            __NAMESPACE__, __CLASS__ . '::frontendShowKamenz'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/SecondarySchool', __CLASS__ . '::frontendValidateSecondarySchool'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/PrimarySchool', __CLASS__ . '::frontendValidatePrimarySchool'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/GrammarSchool', __CLASS__ . '::frontendValidateGrammarSchool'
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
            'Grundschule', '/Document/Standard/KamenzReport/Validate/PrimarySchool'
        ));

        $Stage->addButton(new Standard(
            'Oberschule / Mittelschule', '/Document/Standard/KamenzReport/Validate/SecondarySchool'
        ));

        $Stage->addButton(new Standard(
            'Gymnasium', '/Document/Standard/KamenzReport/Validate/GrammarSchool'
        ));

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

        $summary = array();

        $countStudentsWithoutDivision = 0;
        if (($studentsWithoutDivision = KamenzService::getStudentsWithoutDivision($countStudentsWithoutDivision))) {
            $content[] = new LayoutColumn($studentsWithoutDivision);
            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse zugeordnet',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $content[] = new LayoutColumn(
            KamenzService::validate(Type::useService()->getTypeByName('Mittelschule / Oberschule'), $summary)
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $summary
                        ),
                    ))
                ), new Title('Zusammenfassung')),
                new LayoutGroup(array(
                    new LayoutRow(
                        $content
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendValidatePrimarySchool()
    {

        $Stage = new Stage('Kamenz-Statistik', 'Grundschule validieren');

        $Stage->addButton(new External('Herunterladen: Grundschulstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReportGS\Create',
            new Download(), array(), 'Kamenz-Statistik der GS herunterladen'));

        $summary = array();

        $countStudentsWithoutDivision = 0;
        if (($studentsWithoutDivision = KamenzService::getStudentsWithoutDivision($countStudentsWithoutDivision))) {
            $content[] = new LayoutColumn($studentsWithoutDivision);
            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse zugeordnet',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $content[] = new LayoutColumn(
            KamenzService::validate(Type::useService()->getTypeByName('Grundschule'), $summary)
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $summary
                        ),
                    ))
                ), new Title('Zusammenfassung')),
                new LayoutGroup(array(
                    new LayoutRow(
                        $content
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public static function frontendValidateGrammarSchool()
    {

        $Stage = new Stage('Kamenz-Statistik', 'Gymnasium validieren');

        $Stage->addButton(new External('Herunterladen: Gymnasialstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReportGym\Create',
            new Download(), array(), 'Kamenz-Statistik des Gymnasiums herunterladen'));

        $summary = array();

//        Debugger::screenDump(KamenzReportService::setKamenzReportGymContent(array()));

        $countStudentsWithoutDivision = 0;
        if (($studentsWithoutDivision = KamenzService::getStudentsWithoutDivision($countStudentsWithoutDivision))) {
            $content[] = new LayoutColumn($studentsWithoutDivision);
            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse zugeordnet',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $content[] = new LayoutColumn(
            KamenzService::validate(Type::useService()->getTypeByName('Gymnasium'), $summary)
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $summary
                        ),
                    ))
                ), new Title('Zusammenfassung')),
                new LayoutGroup(array(
                    new LayoutRow(
                        $content
                    )
                ))
            ))
        );

        return $Stage;
    }
}