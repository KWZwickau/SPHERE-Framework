<?php

namespace SPHERE\Application\Reporting\Custom\Annaberg\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info;
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
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Annaberg\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $Stage = new Stage();
        $Stage->setTitle('EGE Auswertung');
        $Stage->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $Stage;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendPrintClassList($DivisionId = null)
    {

        $Stage = new Stage('EGE Auswertung', 'Klassenliste zum Ausdrucken');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Annaberg/Person/PrintClassList',
                new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createPrintClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Annaberg/Common/PrintClassList/Download',
                            new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Annaberg/Person/PrintClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    )
                                )
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            if ($tblDivision = Division::useService()->getDivisionById($DivisionId)) {
                $PersonList = Person::useService()->createPrintClassList($tblDivision);

                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

                $tableHead = array(
                    'Number'         => '#',
                    'LastName'       => 'Name',
                    'FirstName'      => 'Vorname',
                    'Address'        => 'Adresse',
                    'Birthday'       => 'Geburtsdatum',
                    'PhoneStudent' => 'Tel. Schüler '.
                        new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                    'PhoneGuardian1' => 'Tel. Sorgeber. 1 '.
                        new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                    'PhoneGuardian2' => 'Tel. Sorgeber. 2 '.
                        new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                );

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                ($tblDivision->getServiceTblYear() ?
                                    new LayoutColumn(
                                        new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                            Panel::PANEL_TYPE_SUCCESS), 4
                                    ) : ''),
                                new LayoutColumn(
                                    new Panel('Klasse', $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ),
                                ($tblDivision->getTypeName() ?
                                    new LayoutColumn(
                                        new Panel('Schulart', $tblDivision->getTypeName(),
                                            Panel::PANEL_TYPE_SUCCESS), 4
                                    ) : ''),
                            )),
                            ($inActivePanel = $this->getInActiveStudentPanel($tblDivision))
                                ? new LayoutRow(new LayoutColumn($inActivePanel))
                                : null
                        )),
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new TableData($PersonList, null, $tableHead,
                                        array(
                                            "pageLength" => -1,
                                            "responsive" => false,
                                            'columnDefs' => array(
                                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                            ),
                                        )
                                    )
                                )
                            )
                        ),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Weiblich', array(
                                        'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 4),
                                new LayoutColumn(
                                    new Panel('Männlich', array(
                                        'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 4),
                                new LayoutColumn(
                                    new Panel('Gesamt', array(
                                        'Anzahl: ' . count($tblPersonList),
                                    ), Panel::PANEL_TYPE_INFO)
                                    , 4)
                            )),
                            new LayoutRow(
                                new LayoutColumn(
                                    (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                        new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                        null)
                                )
                            )
                        ))
                    ))
                );
            } else {
                $Stage->setContent(
                    new Warning('Klasse nicht verfügbar.')
                );
            }
        }

        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|Panel
     */
    private function getInActiveStudentPanel(TblDivision $tblDivision)
    {
        $inActiveStudentList = array();
        if (($tblDivisionStudentAll = Division::useService()->getDivisionStudentAllByDivision($tblDivision, true))) {
            foreach ($tblDivisionStudentAll as $tblDivisionStudent) {
                if ($tblDivisionStudent->isInActive()
                    && ($tblPerson = $tblDivisionStudent->getServiceTblPerson())
                ) {
                    $inActiveStudentList[] = $tblPerson->getLastFirstName() . ' (Deaktivierung: ' . $tblDivisionStudent->getLeaveDate() . ')';
                }
            }
        }

        return empty($inActiveStudentList) ? false : new Panel('Ehemaliger Schüler dieser Klasse', $inActiveStudentList, Panel::PANEL_TYPE_WARNING);
    }
}