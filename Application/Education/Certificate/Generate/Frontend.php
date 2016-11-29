<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.11.2016
 * Time: 08:45
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Education\Certificate\Generate
 */
class Frontend extends Extension
{

    /**
     * @param null $YearId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendGenerate($YearId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Übersicht');
        $tblYear = false;
        if (($tblYearList = Term::useService()->getYearByNow())) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
        }
        if ($YearId) {
            $tblYear = Term::useService()->getYearById($YearId);
        } elseif ($tblYearList) {
            $tblYear = current($tblYearList);
        }

        if ($tblYearList && count($tblYearList) > 1) {
            $tblYearList = $this->getSorter($tblYearList)->sortObjectBy('DisplayName');
            /** @var TblYear $tblYearItem */
            foreach ($tblYearList as $tblYearItem) {
                if ($tblYear && $tblYear->getId() == $tblYearItem->getId()) {
                    $Stage->addButton(new Standard(new Info(new Bold($tblYearItem->getDisplayName())),
                        '/Education/Certificate/Generate', new Edit(), array('YearId' => $tblYearItem->getId())));
                } else {
                    $Stage->addButton(new Standard($tblYearItem->getDisplayName(), '/Education/Certificate/Generate',
                        null, array('YearId' => $tblYearItem->getId())));
                }
            }
        }

        if (!$tblYear) {
            return $Stage . new Danger('Kein Schuljahr verfügbar bzw. kein Schuljahr ausgewählt.', new Exclamation());
        }

        $tableData = array();
        if ($tblYear
            && ($tblGenerateCertificateAllByYear = Generate::useService()->getGenerateCertificateAllByYear($tblYear))
        ) {
            foreach ($tblGenerateCertificateAllByYear as $tblGenerateCertificate) {
                $tableData[] = array(
                    'Date' => $tblGenerateCertificate->getDate(),
                    'Type' => $tblGenerateCertificate->getServiceTblCertificateType()
                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName() : '',
                    'Name' => $tblGenerateCertificate->getName(),
                    'Status' => '',
                    'Option' =>
                        (new Standard(
                            '', '/Education/Certificate/Generate/Division/Select', new Listing(),
                            array(
                                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
                            )
                            , 'Klassen zuordnen'
                        ))
                        . (new Standard(
                            '', '/Education/Certificate/Generate/Division', new Equalizer()
//                            array(
//                                'GenerateCertificateId' => $tblGenerateCertificate->getId(),
//                            )
//                            , 'Einstellungen'
                        ))
                );
            }
        }

        $Form = $this->formGenerate($tblYear ? $tblYear : null)
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Schuljahr',
                                    $tblYear
                                        ? $tblYear->getDisplayName()
                                        : new Exclamation() . new Warning('Kein Schuljahr gewählt/vorhanden'),
                                    Panel::PANEL_TYPE_INFO
                                )
                            ))
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($tableData, null, array(
                                    'Date' => 'Zeugnisdatum',
                                    'Type' => 'Typ',
                                    'Name' => 'Name',
                                    'Status' => 'Status',
                                    'Option' => ''
                                ),
                                    array(
                                        'order' => array(
                                            array(0, 'desc')
                                        ),
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 0)
                                        )
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Generate::useService()->createGenerateCertificate($Form, $Data, $tblYear))
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return Form
     */
    private function formGenerate(TblYear $tblYear = null)
    {

        $tblCertificateTypeAll = Generator::useService()->getCertificateTypeAll();

        $tblAppointedDateTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK'),
            $tblYear ? $tblYear : null
        );
        $tblBehaviorTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK'),
            $tblYear ? $tblYear : null
        );

        $Global = $this->getGlobal();
        if (!$Global->POST) {
            if ($tblAppointedDateTaskListByYear) {
                $tblAppointedDateTask = reset($tblAppointedDateTaskListByYear);
            } else {
                $tblAppointedDateTask = false;
            }
            $Global->POST['Data']['AppointedDateTask'] = $tblAppointedDateTask ? $tblAppointedDateTask->getId() : 0;

            if ($tblBehaviorTaskListByYear) {
                $tblBehaviorTask = reset($tblBehaviorTaskListByYear);
            } else {
                $tblBehaviorTask = false;
            }
            $Global->POST['Data']['BehaviorTask'] = $tblBehaviorTask ? $tblBehaviorTask->getId() : 0;

            // Halbjahr oder Jahreszeugnis vorauswählen an Hand des aktuellen Datums
            if (($tblPeriodList = $tblYear->getTblPeriodAll())
                && count($tblPeriodList) == 2
            ) {
                $tblCurrentPeriod = false;
                foreach ($tblPeriodList as $tblPeriod) {
                    if ($tblPeriod->getFromDate() && $tblPeriod->getToDate()) {
                        $fromDate = (new \DateTime($tblPeriod->getFromDate()))->format("Y-m-d");
                        $toDate = (new \DateTime($tblPeriod->getToDate()))->format("Y-m-d");
                        $now = (new \DateTime('now'))->format("Y-m-d");
                        if ($fromDate <= $now && $now <= $toDate) {
                            $tblCurrentPeriod = $tblPeriod;
                            break;
                        }
                    }
                }

                if ($tblCurrentPeriod) {
                    if ($tblPeriodList[0]->getFromDate() && $tblPeriodList[1]->getFromDate()
                        && (new \DateTime($tblPeriodList[0]->getFromDate()))->format("Y-m-d")
                        < (new \DateTime($tblPeriodList[1]->getFromDate()))->format("Y-m-d")
                    ) {
                        $tblFirstPeriod = $tblPeriodList[0];
                        $tblSecondPeriod = $tblPeriodList[1];
                    } else {
                        $tblFirstPeriod = $tblPeriodList[1];
                        $tblSecondPeriod = $tblPeriodList[0];
                    }

                    if ($tblFirstPeriod->getId() == $tblCurrentPeriod->getId()) {
                        $Global->POST['Data']['Type'] = Generator::useService()->getCertificateTypeByIdentifier('HALF_YEAR');
                    } elseif ($tblSecondPeriod->getId() == $tblCurrentPeriod->getId()) {
                        $Global->POST['Data']['Type'] = Generator::useService()->getCertificateTypeByIdentifier('YEAR');
                    }
                }
            }

            $Global->savePost();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()), 6
                ),
                new FormColumn(
                    new SelectBox('Data[Type]', 'Typ', array('Name' => $tblCertificateTypeAll)), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new SelectBox('Data[AppointedDateTask]', 'Stichtagsnotenauftrag',
                        array('{{ Date }} {{ Name }}' => $tblAppointedDateTaskListByYear)), 6
                ),
                new FormColumn(
                    new SelectBox('Data[BehaviorTask]', 'Kopfnotenauftrag',
                        array('{{ Date }} {{ Name }}' => $tblBehaviorTaskListByYear)), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Data[HeadmasterName]', '', 'Name des/der Schulleiter/in'), 12
                ),
                new FormColumn(
                    new CheckBox('Data[IsTeacherAvailable]', 'Name des Klassenlehrers auf dem Zeugnis anzeigen', 1), 12
                ),
            )),
        )));
    }

    /**
     * @param null $GenerateCertificateId
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendSelectDivision($GenerateCertificateId = null, $Data = null)
    {

        $Stage = new Stage('Zeugnis generieren', 'Klassen zuordnen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        if (($tblGenerateCertificate = Generate::useService()->getGenerateCertificateById($GenerateCertificateId))) {

            $divisionExistsList = array();
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                if (($tblPrepareList = Prepare::useService()->getPrepareAllByGenerateCertificate($tblGenerateCertificate))) {
                    foreach ($tblPrepareList as $tblPrepareCertificate) {
                        if (($tblDivision = $tblPrepareCertificate->getServiceTblDivision())) {
                            $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                            $divisionExistsList[$tblDivision->getId()] = $tblDivision;
                        }
                    }
                } else {
                    // vorselektieren anhand der Notenaufträge
                    if (($tblGenerateCertificate->getServiceTblAppointedDateTask())) {
                        $tblTestAllByTest = Evaluation::useService()->getTestAllByTask(
                            $tblGenerateCertificate->getServiceTblAppointedDateTask()
                        );
                        if ($tblTestAllByTest) {
                            foreach ($tblTestAllByTest as $tblTest) {
                                if (($tblDivision = $tblTest->getServiceTblDivision())) {
                                    $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                                }
                            }
                        }
                    }
                    if (($tblGenerateCertificate->getServiceTblBehaviorTask())) {
                        $tblTestAllByTest = Evaluation::useService()->getTestAllByTask(
                            $tblGenerateCertificate->getServiceTblBehaviorTask()
                        );
                        if ($tblTestAllByTest) {
                            foreach ($tblTestAllByTest as $tblTest) {
                                if (($tblDivision = $tblTest->getServiceTblDivision())) {
                                    $Global->POST['Data']['Division'][$tblDivision->getId()] = 1;
                                }
                            }
                        }
                    }
                }
            }
            $Global->savePost();

            $schoolTypeList = array();
            if ($tblGenerateCertificate->getServiceTblYear()) {
                $tblDivisionAllByYear = Division::useService()->getDivisionByYear($tblGenerateCertificate->getServiceTblYear());
                if ($tblDivisionAllByYear) {
                    foreach ($tblDivisionAllByYear as $tblDivision) {
                        $type = $tblDivision->getTblLevel()->getServiceTblType();
                        $tblDivisionSubjectList = Division::useService()->getDivisionSubjectByDivision($tblDivision);
                        if ($type && $tblDivisionSubjectList) {
                            $schoolTypeList[$type->getId()][$tblDivision->getId()] = $tblDivision->getDisplayName();
                        }
                    }
                }
            }

            $columnList = array();
            if (!empty($schoolTypeList)) {
                foreach ($schoolTypeList as $typeId => $divisionList) {
                    $type = Type::useService()->getTypeById($typeId);
                    if ($type && is_array($divisionList)) {

                        asort($divisionList, SORT_NATURAL);

                        $checkBoxList = array();
                        foreach ($divisionList as $key => $value) {
                            if (isset($divisionExistsList[$key])) {
                                $checkBoxList[] = (new CheckBox('Data[Division][' . $key . ']', $value,
                                    1))->setDisabled();
                            } else {
                                $checkBoxList[] = new CheckBox('Data[Division][' . $key . ']', $value, 1);
                            }
                        }

                        $panel = new Panel($type->getName(), $checkBoxList, Panel::PANEL_TYPE_DEFAULT);
                        $columnList[] = new FormColumn($panel, 3);
                    }
                }
            }

            $form = new Form(array(
                new FormGroup(
                    new FormRow(
                        $columnList
                    )
                    , new \SPHERE\Common\Frontend\Form\Repository\Title('Klassen'))
            ));
            $form
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', $tblGenerateCertificate->getDate(), Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ',
                                    $tblGenerateCertificate->getServiceTblCertificateType()
                                        ? $tblGenerateCertificate->getServiceTblCertificateType()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Stichtagsnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblAppointedDateTask()
                                        ? $tblGenerateCertificate->getServiceTblAppointedDateTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Kopfnotenauftrag',
                                    $tblGenerateCertificate->getServiceTblBehaviorTask()
                                        ? $tblGenerateCertificate->getServiceTblBehaviorTask()->getName()
                                        : ''
                                    , Panel::PANEL_TYPE_INFO)
                            ), 3),
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Well(Generate::useService()->createPrepareCertificates($form, $tblGenerateCertificate, $Data))
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Zeugniserstellung nicht gefunden', new Exclamation());
        }
    }


    /**
     * @return Stage
     */
    public function frontendDivision()
    {

        $Stage = new Stage('Zeugnis', 'Klassenübersicht');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate', new ChevronLeft()));

        $tableData = array();

        $tableData[] = array(
            'Division' => '5a',
            'Status' => new Warning(new Ban() . ' 14 von 17 Zeugnisvorlagen ausgewählt'),
            'Option' => new Standard('', '/Education/Certificate/Generate/Division/SelectTemplate', new Edit())
        );
        $tableData[] = array(
            'Division' => '5b',
            'Status' => new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' 18 von 18 Zeugnisvorlagen ausgewählt'),
            'Option' => new Standard('', '/Education/Certificate/Generate/Division/SelectTemplate', new Edit())
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Zeugnisdatum', '31.01.2017', Panel::PANEL_TYPE_INFO)
                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Typ', 'Halbjahreszeugnis/Halbjahresinformation', Panel::PANEL_TYPE_INFO)
                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Name', 'SN Noteninfo, KN Noteninfo', Panel::PANEL_TYPE_INFO)
                        ), 6)
                    ))
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData(
                                $tableData, null, array(
                                    'Division' => 'Klasse',
                                    'Status' => 'Zeugnisvorlagen',
                                    'Option' => ''
                                )
                            )
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage|string
     */
    public function frontendSelectTemplate()
    {

        $Stage = new Stage('Zeugnis', 'Vorlagen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Education/Certificate/Generate/Division', new ChevronLeft()));

        $tblDivision = Division::useService()->getDivisionById(6);
        if ($tblDivision) {
            $tableData = array();
            if (($tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision))) {
                $count = 1;
                foreach ($tblStudentList as $tblPerson) {
                    if ($count > 3) {
                        $tableData[] = array(
                            'Number' => $count++,
                            'Student' => $tblPerson->getLastFirstName(),
                            'Template' => 'Halbjahresinformation Mittelschule Realschule'
                        );
                    } else {
                        $tableData[] = array(
                            'Number' => $count++,
                            'Student' => $tblPerson->getLastFirstName(),
                            'Template' => new SelectBox('Data[' . $tblPerson->getId() . ']', '', array(
                                    1 => 'Halbjahresinformation Mittelschule Hauptschule',
                                    2 => 'Halbjahresinformation Mittelschule Realschule',
                                )
                            )
                        );
                    }
                }
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TableData(
                                $tableData, null, array(
                                    'Number' => 'Nr.',
                                    'Student' => 'Schüler',
                                    'Template' => 'Zeugnisvorlage'
                                )
                            )
                        )
                    )
                )
            );
            $form->appendFormButton(new Primary('Speichern', new Save()));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel('Zeugnisdatum', '31.01.2017', Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Typ', 'Halbjahreszeugnis/Halbjahresinformation', Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Name', 'SN Noteninfo, KN Noteninfo', Panel::PANEL_TYPE_INFO)
                            ), 3),
                            new LayoutColumn(array(
                                new Panel('Division', $tblDivision->getDisplayName(), Panel::PANEL_TYPE_INFO)
                            ), 3)
                        ))
                    )),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                $form
                            )),
                        ))
                    ))
                ))
            );

            return $Stage;
        } else {
            return new Danger('Klasse nicht gefunden', new Exclamation());
        }
    }
}