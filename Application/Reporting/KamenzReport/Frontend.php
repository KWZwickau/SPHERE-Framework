<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 16.08.2017
 * Time: 09:22
 */

namespace SPHERE\Application\Reporting\KamenzReport;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
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
    public static function frontendShowKamenz(): Stage
    {
        $Stage = new Stage('Kamenz-Statistik', 'Auswählen');

        $typeList = School::useService()->getConsumerSchoolTypeAll();

        if (!$typeList || isset($typeList['GS'])) {
            $Stage->addButton(new Standard(
                'Grundschule', __NAMESPACE__ . '/Validate/PrimarySchool'
            ));
        }

        if (!$typeList || isset($typeList['OS'])) {
            $Stage->addButton(new Standard(
                TblType::IDENT_OBER_SCHULE, __NAMESPACE__ . '/Validate/SecondarySchool'
            ));
        }

        if (!$typeList || isset($typeList['Gy'])) {
            $Stage->addButton(new Standard(
                'Gymnasium', __NAMESPACE__ . '/Validate/GrammarSchool'
            ));
        }

        if (!$typeList || isset($typeList['BFS'])) {
            $Stage->addButton(new Standard(
                'Berufsfachschule', __NAMESPACE__ . '/Validate/TechnicalSchool'
            ));
        }

        if (!$typeList || isset($typeList['FS'])) {
            $Stage->addButton(new Standard(
                'Fachschule', __NAMESPACE__ . '/Validate/AdvancedTechnicalSchool'
            ));
        }

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
    public static function frontendValidateSecondarySchool(): Stage
    {
        $Stage = new Stage('Kamenz-Statistik', TblType::IDENT_OBER_SCHULE . ' validieren');
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
            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse/Stammgruppe zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse/Stammgruppe zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $countStudentsWithoutSchoolTypeOrLevel = 0;
        if (($studentsWithoutSchoolTypeOrLevel = KamenzService::getStudentsWithoutSchoolTypeOrLevel($countStudentsWithoutSchoolTypeOrLevel))) {
            $content[] = new LayoutColumn($studentsWithoutSchoolTypeOrLevel);
            $summary[] = new Warning($countStudentsWithoutSchoolTypeOrLevel . ' Schüler sind keiner aktuellen Klassenstufe oder Schulart zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klassenstufe und Schulart zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $content[] = new LayoutColumn(
            KamenzService::validate(Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE), $summary)
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
    public static function frontendValidatePrimarySchool(): Stage
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
            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse/Stammgruppe zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse/Stammgruppe zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $countStudentsWithoutSchoolTypeOrLevel = 0;
        if (($studentsWithoutSchoolTypeOrLevel = KamenzService::getStudentsWithoutSchoolTypeOrLevel($countStudentsWithoutSchoolTypeOrLevel))) {
            $content[] = new LayoutColumn($studentsWithoutSchoolTypeOrLevel);
            $summary[] = new Warning($countStudentsWithoutSchoolTypeOrLevel . ' Schüler sind keiner aktuellen Klassenstufe oder Schulart zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klassenstufe und Schulart zugeordnet.',
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
    public static function frontendValidateGrammarSchool(): Stage
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

//        $Content = array();
//        $Content = KamenzReportService::setKamenzReportGymContent($Content);
//        Debugger::devDump($Content);

        $summary = array();

        $countStudentsWithoutDivision = 0;
        if (($studentsWithoutDivision = KamenzService::getStudentsWithoutDivision($countStudentsWithoutDivision))) {
            $content[] = new LayoutColumn($studentsWithoutDivision);
            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse/Stammgruppe zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse/Stammgruppe zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $countStudentsWithoutSchoolTypeOrLevel = 0;
        if (($studentsWithoutSchoolTypeOrLevel = KamenzService::getStudentsWithoutSchoolTypeOrLevel($countStudentsWithoutSchoolTypeOrLevel))) {
            $content[] = new LayoutColumn($studentsWithoutSchoolTypeOrLevel);
            $summary[] = new Warning($countStudentsWithoutSchoolTypeOrLevel . ' Schüler sind keiner aktuellen Klassenstufe oder Schulart zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klassenstufe und Schulart zugeordnet',
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

    /**
     * @return Stage
     */
    public static function frontendValidateTechnicalSchool(): Stage
    {
        $Stage = new Stage('Kamenz-Statistik', 'Berufsfachschule validieren');
        $Stage->addButton(new Standard('Zurück', '/Reporting/KamenzReport', new ChevronLeft()));

        $Stage->addbutton(new External('Herunterladen: Berufsfachschulstatistik Teil I',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Berufsfachschule',
                'Part' => '1'
            ),
            'Kamenz-Statistik Teil I herunterladen'
        ));
        $Stage->addbutton(new External('Herunterladen: Berufsfachschulstatistik Teil II',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Berufsfachschule',
                'Part' => '2'
            ),
            'Kamenz-Statistik Teil II herunterladen'
        ));
        $Stage->addbutton(new External('Herunterladen: Berufsfachschulstatistik Teil III',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Berufsfachschule',
                'Part' => '3'
            ),
            'Kamenz-Statistik Teil III herunterladen'
        ));

        $summary = array();

//        $countStudentsWithoutDivision = 0;
//        if (($studentsWithoutDivision = KamenzService::getStudentsWithoutDivision($countStudentsWithoutDivision))) {
//            $content[] = new LayoutColumn($studentsWithoutDivision);
//            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse/Stammgruppe zugeordnet.'
//                , new Exclamation());
//        } else {
//            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse/Stammgruppe zugeordnet',
//                new \SPHERE\Common\Frontend\Icon\Repository\Success());
//        }

        $countStudentsWithoutSchoolTypeOrLevel = 0;
        if (($studentsWithoutSchoolTypeOrLevel = KamenzService::getStudentsWithoutSchoolTypeOrLevel($countStudentsWithoutSchoolTypeOrLevel))) {
            $content[] = new LayoutColumn($studentsWithoutSchoolTypeOrLevel);
            $summary[] = new Warning($countStudentsWithoutSchoolTypeOrLevel . ' Schüler sind keiner aktuellen Klassenstufe oder Schulart zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klassenstufe und Schulart zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $content[] = new LayoutColumn(
            KamenzService::validate(Type::useService()->getTypeByName('Berufsfachschule'), $summary)
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
    public static function frontendValidateAdvancedTechnicalSchool(): Stage
    {
        $Stage = new Stage('Kamenz-Statistik', 'Fachschule validieren');
        $Stage->addButton(new Standard('Zurück', '/Reporting/KamenzReport', new ChevronLeft()));

        $Stage->addbutton(new External('Herunterladen: Fachschulstatistik Teil I',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Fachschule',
                'Part' => '1'
            ),
            'Kamenz-Statistik Teil I herunterladen'
        ));
        $Stage->addbutton(new External('Herunterladen: Fachschulstatistik Teil II',
            'SPHERE\Application\Api\Document\Standard\KamenzReport\Create',
            new Download(),
            array(
                'Type' => 'Fachschule',
                'Part' => '2'
            ),
            'Kamenz-Statistik Teil II herunterladen'
        ));

        $summary = array();

//        $countStudentsWithoutDivision = 0;
//        if (($studentsWithoutDivision = KamenzService::getStudentsWithoutDivision($countStudentsWithoutDivision))) {
//            $content[] = new LayoutColumn($studentsWithoutDivision);
//            $summary[] = new Warning($countStudentsWithoutDivision . ' Schüler sind keiner aktuellen Klasse/Stammgruppe zugeordnet.'
//                , new Exclamation());
//        } else {
//            $summary[] = new Success('Alle Schüler sind einer aktuellen Klasse/Stammgruppe zugeordnet',
//                new \SPHERE\Common\Frontend\Icon\Repository\Success());
//        }

        $countStudentsWithoutSchoolTypeOrLevel = 0;
        if (($studentsWithoutSchoolTypeOrLevel = KamenzService::getStudentsWithoutSchoolTypeOrLevel($countStudentsWithoutSchoolTypeOrLevel))) {
            $content[] = new LayoutColumn($studentsWithoutSchoolTypeOrLevel);
            $summary[] = new Warning($countStudentsWithoutSchoolTypeOrLevel . ' Schüler sind keiner aktuellen Klassenstufe oder Schulart zugeordnet.'
                , new Exclamation());
        } else {
            $summary[] = new Success('Alle Schüler sind einer aktuellen Klassenstufe und Schulart zugeordnet.',
                new \SPHERE\Common\Frontend\Icon\Repository\Success());
        }

        $content[] = new LayoutColumn(
            KamenzService::validate(Type::useService()->getTypeByName('Fachschule'), $summary)
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