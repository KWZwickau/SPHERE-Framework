<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 18.12.2018
 * Time: 09:38
 */

namespace SPHERE\Application\Reporting\Custom\BadDueben\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\BadDueben\Person
 */
class Frontend  extends Extension implements IFrontendInterface
{

    /**
     * @param null $LevelId
     * @param null $YearId
     *
     * @return Stage
     */
    public function frontendClassList($LevelId = null, $YearId = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenliste');
        if (null !== $LevelId || $YearId !== null) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/BadDueben/Person/ClassList', new ChevronLeft()));
        }
        $tblLevelAll = Division::useService()->getLevelAll();
        $LevelList = array();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel $tblLevel) use (&$LevelList) {
                $LevelList[$tblLevel->getName()][] = $tblLevel;
            });
        }

        // show level table
        if ($LevelId === null && $YearId === null) {
            $TableContent = array();
            if (!empty($LevelList)) {
                array_walk($LevelList, function ($tblLevelList) use (&$TableContent) {
                    $tblDivisionList = array();

                    /** @var TblLevel $tblLevel */
                    foreach ($tblLevelList as $tblLevel) {
                        $DivisionArray = Division::useService()->getDivisionByLevel($tblLevel);
                        if ($DivisionArray) {
                            foreach ($DivisionArray as $tblDivision) {
                                $tblDivisionList[] = $tblDivision;
                            }
                        }
                    }

                    $DivisionYearList = array();
                    if ($tblDivisionList) {
                        /** @var TblDivision $tblDivision */
                        foreach ($tblDivisionList as $tblDivision) {
                            $tblYear = $tblDivision->getServiceTblYear();
                            if ($tblYear) {
                                $DivisionYearList[$tblYear->getId()]['DivisionName'][$tblDivision->getId()] = $tblDivision->getDisplayName();
                                $DivisionYearList[$tblYear->getId()]['DivisionType'][$tblDivision->getTypeName()] = $tblDivision->getTypeName();
                                $DivisionYearList[$tblYear->getId()]['Count'][$tblDivision->getId()] =
                                    Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                            }
                        }
                    }
                    if (!empty($DivisionYearList)) {
                        foreach ($DivisionYearList as $Key => $DivisionList) {
                            $tblYear = Term::useService()->getYearById($Key);
                            $Item['Year'] = $tblYear->getDisplayName();
                            $Item['Division'] = '';
                            $Item['Type'] = '';
                            $Item['Count'] = '';
                            if (isset($DivisionList['DivisionName'])) {
                                $Item['Division'] = implode(', ', $DivisionList['DivisionName']);
                            }
                            if (isset($DivisionList['DivisionType'])) {
                                sort($DivisionList['DivisionType']);
                                $Item['Type'] = implode(', ', $DivisionList['DivisionType']);
                            }
                            if (isset($DivisionList['Count'])) {
                                $Item['Count'] = array_sum($DivisionList['Count']);
                            }
                            $Item['Option'] = new Standard('', '/Reporting/Custom/BadDueben/Person/ClassList', new EyeOpen(),
                                array('LevelId' => $tblLevel->getId(), 'YearId'  => $tblYear->getId()), 'Anzeigen');

                            array_push($TableContent, $Item);
                        }
                    }
                });
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse(n)',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => '',
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(1, 'asc'),
                                            array(2, 'asc'),
                                        ),
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $PersonList = array();
            $tblLevel = Division::useService()->getLevelById($LevelId);
            $tblYear = Term::useService()->getYearById($YearId);

            // show download button
            if ($LevelId !== null && $YearId !== null) {
                $Global = $this->getGlobal();
                if (!$Global->POST) {
                    $Global->POST['Select']['Level'] = $LevelId;
                    $Global->POST['Select']['Year'] = $YearId;
                    $Global->savePost();
                }
                if ($tblLevel && $tblYear) {
                    $tblDivisionList = Division::useService()->getDivisionAllByLevelNameAndYear($tblLevel, $tblYear);
                    if (!empty($tblDivisionList)) {
                        $PersonList = Person::useService()->createClassList($tblDivisionList);
                        if ($PersonList) {
                            $Stage->addButton(
                                new Primary('Herunterladen',
                                    '/Api/Reporting/Custom/BadDueben/Common/ClassList/Download',
                                    new Download(),
                                    array('LevelId' => $tblLevel->getId(),
                                        'YearId'  => $tblYear->getId()))
                            );
                            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                            ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                        }
                    }
                }
            }

            // show reporting from level
            $tblDivisionList = array();
            if ($tblLevel && $tblYear) {
                $tblDivisionList = Division::useService()->getDivisionAllByLevelNameAndYear($tblLevel, $tblYear);
            }

            $SchoolTypeList = array();
            $tblPersonList = false;
            if (!empty($tblDivisionList)) {
                foreach ($tblDivisionList as $tblDivision) {
                    $SchoolTypeList[$tblDivision->getTypeName()] = $tblDivision->getTypeName();
                }
                $tblPersonList = Division::useService()->getPersonAllByDivisionList($tblDivisionList);
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblYear ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblYear->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            ( $tblLevel
                                ? new LayoutColumn(
                                    new Panel('Stufe', $tblLevel->getName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            ( !empty($SchoolTypeList) ?
                                new LayoutColumn(
                                    new Panel(( count($SchoolTypeList) == 1 ? 'Schulart' : 'Schularten' ), $SchoolTypeList,
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Division'              => 'Klasse(n)',
                                        'Type'                  => 'Schulart',
                                        'Mentor'                => 'Gruppe',
                                        'Gender'                => 'Geschlecht',
                                        'LastName'              => 'Nachname',
                                        'FirstName'             => 'Vorname',
                                        'StreetName'            => 'Straße',
                                        'StreetNumber'          => 'Nr.',
                                        'Code'                  => 'PLZ',
                                        'City'                  => 'Wohnort',
                                        'District'              => 'Ortsteil',
                                        'PhoneNumbersPrivate'   => 'privat',
                                        'PhoneNumbersBusiness'  => 'dienstlich M.',
                                        'PhoneNumbersGuardian1' => 'Mutter',
                                        'PhoneNumbersGuardian2' => 'Vater',
                                        'MailAddress'           => 'E-Mail',
                                        'Birthday'              => 'Geb.-Datum',
                                        'Birthplace'              => 'Geb.-Ort',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false,
                                        'order'      => array(
                                            array(2, 'asc'),
                                            array(4, 'asc'),
                                        ),
                                        "columnDefs" => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(4,5)),
                                            array('type' => 'natural', 'targets' => 7),
                                        ),
                                    )
                                )
                            )
                        )
                    ),
                    ( $tblPersonList
                        ? new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Weiblich', array(
                                        'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 4),
                                new LayoutColumn(
                                    new Panel('Männlich', array(
                                        'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 4),
                                new LayoutColumn(
                                    new Panel('Gesamt', array(
                                        'Anzahl: '.count($tblPersonList),
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 4)
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                        new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                        entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                        in den Stammdaten der Personen.') :
                                        null )
                                )
                            )
                        ))
                        : '' )
                ))
            );
        }

        return $Stage;
    }
}
