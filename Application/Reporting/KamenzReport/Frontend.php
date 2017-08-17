<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 16.08.2017
 * Time: 09:22
 */

namespace SPHERE\Application\Reporting\KamenzReport;

use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Extension\Extension;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\KamenzReport
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public static function frontendShowKamenz()
    {

        $Stage = new Stage('Kamenz-Statistik', 'Auswählen');

        $Stage->addButton(new Standard(
            'Grundschule', __NAMESPACE__ . '/Validate/PrimarySchool'
        ));

        $Stage->addButton(new Standard(
            'Oberschule / Mittelschule',  __NAMESPACE__ . '/Validate/SecondarySchool'
        ));

        $Stage->addButton(new Standard(
            'Gymnasium',  __NAMESPACE__ . '/Validate/GrammarSchool'
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
        $Stage->addButton(new Standard('Zurück', '/Reporting/KamenzReport', new ChevronLeft()));

        $Stage->addbutton(new External('Herunterladen: Oberschulstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Oberschule'
            ),
            'Kamenz-Statistik herunterladen'
        ));

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
        $Stage->addButton(new Standard('Zurück', '/Reporting/KamenzReport', new ChevronLeft()));

        $Stage->addButton(new External('Herunterladen: Grundschulstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Grundschule'
            ),
            'Kamenz-Statistik der Grundschule herunterladen'
        ));

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
        $Stage->addButton(new Standard('Zurück', '/Reporting/KamenzReport', new ChevronLeft()));

        $Stage->addButton(new External('Herunterladen: Gymnasialstatistik',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Gymnasium'
            ),
            'Kamenz-Statistik des Gymnasiums herunterladen'
        ));

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