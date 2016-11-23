<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 23.11.2016
 * Time: 08:45
 */

namespace SPHERE\Application\Education\Certificate\Generate;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Lesson\Division\Division;
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
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
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

    public function frontendGenerate($Data = null)
    {

        // Todo year

        $Stage = new Stage('Zeugnisse', 'Übersicht');

        $tableData = array();

        $tableData[] = array(
            'Date' => '31.01.2017',
            'Type' => 'Halbjahreszeugnis/Halbjahresinformation',
            'Name' => 'SN Noteninfo, KN Noteninfo',
            'Status' => new Warning(new Ban() . ' nicht alle Zeugnisvorlagen ausgewählt'),
            'Option' => new Standard(
                'Zeugnisvorlagen auswählen', '/Education/Certificate/Generate/Division'
            )
        );

//        $tblPrepareAllByDivision = Prepare::useService()->getPrepareAllByDivision($tblDivision);
//        if ($tblPrepareAllByDivision) {
//            foreach ($tblPrepareAllByDivision as $tblPrepare) {
//
//                $tableData[] = array(
//                    'Date' => $tblPrepare->getDate(),
//                    'Name' => $tblPrepare->getName(),
//                    'Status' => $tblPrepare->isAppointedDateTaskUpdated()
//                        ? new \SPHERE\Common\Frontend\Text\Repository\Warning(new Exclamation() . ' Stichtagsnotenauftrag wurde aktualisiert')
//                        : new Success(new Enable() . ' Keine Fachnoten-Änderungen'),
//                    'Option' =>
//                        (new Standard(
//                            '', '/Education/Certificate/Prepare/Prepare/Edit', new Edit(),
//                            array(
//                                'PrepareId' => $tblPrepare->getId(),
//                            )
//                            , 'Bearbeiten'
//                        ))
//                        . (new Standard(
//                            '', '/Education/Certificate/Prepare/Division', new Setup(),
//                            array(
//                                'PrepareId' => $tblPrepare->getId(),
//                            )
//                            , 'Einstellungen'
//                        ))
//                );
//            }
//        }

        $Form = $this->formGenerate()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Schuljahr',
                                    '',
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
                                new Well($Form)
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formGenerate()
    {

        $CertificateTypeList[1] = 'Halbjahreszeugnis/Halbjahresinformation';
        $CertificateTypeList[2] = 'Jahreszeugnis';
        $CertificateTypeList[3] = 'Abschlusszeugnis';
        $CertificateTypeList[4] = 'Noteninformation';
        $CertificateTypeList[5] = 'Bildungsempfehlung';

        // Todo Year
        $tblAppointedDateTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('APPOINTED_DATE_TASK')
        );
        $tblBehaviorTaskListByYear = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR_TASK')
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

            $Global->savePost();
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Data[Date]', '', 'Zeugnisdatum', new Calendar()), 6
                ),
                new FormColumn(
                    new SelectBox('Data[Type]', 'Typ', $CertificateTypeList), 6
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
                    new TextField('Data[Headmaster]', '', 'Name des/der Schulleiter/in'), 12
                ),
                new FormColumn(
                    new CheckBox('Data[IsTeacherAvailable]', 'Name des Klassenlehrers auf dem Zeugnis anzeigen', 1), 12
                ),
            )),
        )));
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