<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendAbsence($DivisionId = null, $PersonId = null, $Data = null)
    {

        $Stage = new Stage('Fehlzeiten', 'Übersicht');
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister/Selected', new ChevronLeft(),
                array('DivisionId' => $tblDivision->getId())
            ));
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister', new ChevronLeft()
            ));
        }
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if ($tblPerson && $tblDivision) {

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Status'] = TblAbsence::VALUE_STATUS_EXCUSED;
                $Global->savePost();
            }

            $tableData = array();
            $tblAbsenceAllByPerson = Absence::useService()->getAbsenceAllByPerson($tblPerson, $tblDivision);
            if ($tblAbsenceAllByPerson) {
                foreach ($tblAbsenceAllByPerson as $tblAbsence) {
                    $status = '';
                    if ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                        $status = new Success('entschuldigt');
                    } elseif ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                        $status = new \SPHERE\Common\Frontend\Text\Repository\Danger('unentschuldigt');
                    }
                    // ToDo JohK Tage richtig berechnen
                    $tableData[] = array(
                        'FromDate' => $tblAbsence->getFromDate(),
                        'ToDate' => $tblAbsence->getToDate(),
                        'Days' => $tblAbsence->getDays(),
                        'Remark' => $tblAbsence->getRemark(),
                        'Status' => $status,
                        'Option' => new Standard(
                            '', '/Education/ClassRegister/Absence/Edit', new Edit(),
                            array('Id' => $tblAbsence->getId()), 'Bearbeiten'
                        )
                    );
                }
            }

            $Form = $this->formAbsence()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new TableData($tableData, null, array(
                                        'FromDate' => 'Datum von',
                                        'ToDate' => 'Datum bis',
                                        'Days' => 'Tage',
                                        'Remark' => 'Bemerkung',
                                        'Status' => 'Status',
                                        'Option' => ''
                                    ),
                                        array(
                                            'order' => array(
                                                array(0, 'desc')
                                            ),
                                            'columnDefs' => array(
                                                array('type' => 'de_date', 'targets' => 0),
                                                array('type' => 'de_date', 'targets' => 1),
                                            )
                                        )
                                    )
                                ))
                            ))
                        ), new Title(new ListingTable() . ' Übersicht')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Absence::useService()->createAbsence($Form, $tblPerson, $tblDivision,
                                        $Data))
                                )
                            ))
                        ), new Title(new PlusSign() . ' Hinzufügen'))
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @return Form
     */
    private function formAbsence()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 6
                ),
                new FormColumn(
                    new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 6
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('Data[Remark]', '', 'Bemerkung'), 12
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Status',
                        array(
                            new RadioBox('Data[Status]', 'entschuldigt', TblAbsence::VALUE_STATUS_EXCUSED),
                            new RadioBox('Data[Status]', 'unentschuldigt', TblAbsence::VALUE_STATUS_UNEXCUSED)
                        ),
                        Panel::PANEL_TYPE_INFO
                    )
                ),
            )),
        )));
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditAbsence($Id = null, $Data = null)
    {

        $Stage = new Stage('Fehlzeiten', 'Bearbeiten');
        $tblAbsence = Absence::useService()->getAbsenceById($Id);
        if ($tblAbsence) {
            $tblDivision = $tblAbsence->getServiceTblDivision();
            $tblPerson = $tblAbsence->getServiceTblPerson();
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister/Absence', new ChevronLeft(),
                array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision->getId()
                )
            ));

            if ($Data === null) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['FromDate'] = $tblAbsence->getFromDate();
                $Global->POST['Data']['ToDate'] = $tblAbsence->getToDate();
                $Global->POST['Data']['Remark'] = $tblAbsence->getRemark();
                $Global->POST['Data']['Status'] = $tblAbsence->getStatus();
                $Global->savePost();
            }

            $Form = $this->formAbsence()
                ->appendFormButton(new Primary('Speichern', new Save()))
                ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Well(Absence::useService()->updateAbsence($Form, $tblAbsence, $Data))
                                )
                            ))
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister', new ChevronLeft()
            ));

            return $Stage . new Danger('Fehlzeit nicht gefunden.', new Ban());
        }
    }

    public function frontendAbsenceMonth($DivisionId = null)
    {

        $Stage = new Stage('Fehlzeiten', 'Monatsübersicht');
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister/Selected', new ChevronLeft(),
                array('DivisionId' => $tblDivision->getId())
            ));

            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ))
                            ))
                        )),
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister', new ChevronLeft()
            ));

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }
}