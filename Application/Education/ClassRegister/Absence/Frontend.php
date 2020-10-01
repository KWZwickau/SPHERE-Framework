<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $DivisionId
     * @param null $PersonId
     * @param string $BasicRoute
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendAbsence(
        $DivisionId = null,
        $PersonId = null,
        $BasicRoute = '/Education/ClassRegister/Teacher',
        $Data = null
    ) {

        $Stage = new Stage('Fehlzeiten', 'Übersicht');
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $Stage->addButton(new Standard(
                'Zurück', $BasicRoute . '/Selected', new ChevronLeft(),
                array('DivisionId' => $tblDivision->getId())
            ));
        } else {
            $Stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
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

                    $tableData[] = array(
                        'FromDate' => $tblAbsence->getFromDate(),
                        'ToDate' => $tblAbsence->getToDate(),
                        'Days' => $tblAbsence->getDays(),
                        'Remark' => $tblAbsence->getRemark(),
                        'Status' => $status,
                        'Option' =>
                            (new Standard(
                                '',
                                '/Education/ClassRegister/Absence/Edit',
                                new Edit(),
                                array(
                                    'Id' => $tblAbsence->getId(),
                                    'BasicRoute' => $BasicRoute
                                ),
                                'Bearbeiten'
                            ))
                            . (new Standard(
                                '',
                                '/Education/ClassRegister/Absence/Destroy',
                                new Remove(),
                                array(
                                    'Id' => $tblAbsence->getId(),
                                    'BasicRoute' => $BasicRoute
                                ),
                                'Löschen'
                            ))
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
                                        $BasicRoute, $Data))
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
     * @param bool $hasSearch
     * @param string $Search
     * @param null $Data
     * @param null $AbsenceId
     *
     * @param IMessageInterface|null $message
     * @return Form
     */
    public function formAbsence($hasSearch = false, $Search = '', $Data = null, $AbsenceId = null, IMessageInterface $message = null)
    {
        if ($AbsenceId === null) {
            $global = $this->getGlobal();
            $global->POST['Data']['IsFullDay'] = 1;
            $global->savePost();
        }

        $formRowSearchPerson = new FormRow(array(
            new FormColumn(array(new Panel(
                'Schüler',
                    (new TextField(
                        'Search',
                        '',
                        'Suche',
                        new Search()
                    ))->ajaxPipelineOnKeyUp(ApiAbsence::pipelineSearchPerson())
                    . ApiAbsence::receiverBlock($this->loadPersonSearch($Search, $message), 'SearchPerson')
                , Panel::PANEL_TYPE_INFO
            )))
        ));

//        if ($AbsenceId) {
//            $saveButton = (new PrimaryLink('Speichern', ApiAbsence::getEndpoint(), new Save()))
//                ->ajaxPipelineOnClick(ApiAbsence::pipelineEditAbsenceSave($PersonId, $AbsenceId));
//        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiAbsence::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAbsence::pipelineCreateAbsenceSave());
//        }

        return (new Form(new FormGroup(array(
            $hasSearch ? $formRowSearchPerson : null,
            new FormRow(array(
                new FormColumn(
                    new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 6
                ),
                new FormColumn(
                    new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 6
                ),
            )),
            new FormRow(array(
               new FormColumn(array(
                   (new CheckBox('Data[IsFullDay]', 'ganztägig', 1))->ajaxPipelineOnClick(ApiAbsence::pipelineLoadLesson()),
                   // todo for edit
                   ApiAbsence::receiverBlock($this->loadLesson(true), 'loadLesson')
               ))
            )),
            new FormRow(array(
                new FormColumn(
                    // todo $peronId
                    ApiAbsence::receiverBlock($this->loadType(null), 'loadType')
                )
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
            // todo für normales Formular ohne ajax -> besser wahrscheinlich doch 2 Formulare mit den Bereichen ausgelagert
            new FormRow(array(
                new FormColumn(array(
                    $saveButton
                ))
            ))
        ))))->disableSubmitAction();
    }

    /**
     * @param $Search
     * @param IMessageInterface|null $message
     *
     * @return string
     */
    public function loadPersonSearch($Search, IMessageInterface $message = null)
    {
        if ($Search != '' && strlen($Search) > 2) {
            $resultList = array();
            $result = '';
            if (($tblPersonList = Person::useService()->getPersonListLike($Search))) {
                $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
                foreach ($tblPersonList as $tblPerson) {
                    // nur nach Schülern suchen
                    if (Group::useService()->existsGroupPerson($tblGroup, $tblPerson)) {
                        $radio = (new RadioBox('Data[PersonId]', '&nbsp;', $tblPerson->getId()))->ajaxPipelineOnClick(
                            ApiAbsence::pipelineLoadType()
                        );

                        $resultList[] = array(
                            'Select' => $radio,
                            'FirstName' => $tblPerson->getFirstSecondName(),
                            'LastName' => $tblPerson->getLastName(),
                            'Division' => ($tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
                                ? $tblMainDivision->getDisplayName() : ''
                        );
                    }
                }

                $result = new TableData(
                    $resultList,
                    null,
                    array(
                        'Select' => '',
                        'LastName' => 'Nachname',
                        'FirstName' => 'Vorname',
                        'Division' => 'Klasse'
                    ),
                    array(
                        'order' => array(
                            array(1, 'asc'),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                );
            }

            if (empty($resultList)) {
                $result = new \SPHERE\Common\Frontend\Message\Repository\Warning('Es wurden keine entsprechenden Schüler gefunden.', new Ban());
            }
        } else {
            $result =  new \SPHERE\Common\Frontend\Message\Repository\Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result . ($message ? $message : '');
    }

    /**
     * @param $IsFullDay
     *
     * @return Layout|null
     */
    public function loadLesson($IsFullDay)
    {
        if ($IsFullDay) {
            return null;
        } else {
            $left = array();
            $right = array();
            for ($i = 1; $i < 6; $i++) {
                $left[] = $this->setCheckBoxLesson($i);
                $right[] = $this->setCheckBoxLesson($i + 5);
            }

            return new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            ))));
        }
    }

    /**
     * @param null $PersonId
     *
     * @return SelectBox|null
     */
    public function loadType($PersonId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblMainDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))
            && ($tblLevel = $tblMainDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
            && ($tblSchoolType->getName() == 'Berufliches Gymnasium'
                || $tblSchoolType->getName() == 'Berufsfachschule'
                || $tblSchoolType->getName() == 'Berufsschule'
                || $tblSchoolType->getName() == 'Fachoberschule'
                || $tblSchoolType->getName() == 'Fachschule'
            )
        ) {
            // todo wieder einkommentieren
//            $global = $this->getGlobal();
//            $global->POST['Data']['Type'] = TblAbsence::VALUE_TYPE_THEORY;
//            $global->savePost();

            return new SelectBox('Data[Type]', 'Typ', array(
                TblAbsence::VALUE_TYPE_PRACTICE => 'Praxis',
                TblAbsence::VALUE_TYPE_THEORY => 'Theorie'
            ));
        }

        return null;
    }

    /**
     * @param $i
     *
     * @return CheckBox
     */
    private function setCheckBoxLesson($i)
    {
        return new CheckBox('Data[UE][' . $i . ']', $i . '. Unterrichtseinheit', 1);
    }

    /**
     * @param null $Id
     * @param string $BasicRoute
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditAbsence($Id = null, $BasicRoute = '', $Data = null)
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
                    'DivisionId' => $tblDivision->getId(),
                    'BasicRoute' => $BasicRoute
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
                                    new Well(Absence::useService()->updateAbsence($Form, $tblAbsence, $BasicRoute,
                                        $Data))
                                )
                            ))
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            return $Stage . new Danger('Fehlzeit nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $DivisionId
     * @param null $Month
     * @param null $Year
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendAbsenceMonth($DivisionId = null, $Month = null, $Year = null, $BasicRoute = '')
    {

        $Stage = new Stage('Fehlzeiten', 'Monatsübersicht');
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $Stage->addButton(new Standard(
                'Zurück', $BasicRoute . '/Selected', new ChevronLeft(),
                array('DivisionId' => $tblDivision->getId())
            ));

            $firstMonth = null;
            $firstYear = null;

            $startDate = false;
            $endDate = false;
            $tblYear = $tblDivision->getServiceTblYear();
            if ($tblYear) {
                $tblLevel = $tblDivision->getTblLevel();
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblLevel && $tblLevel->getName() == '12');
                if ($tblPeriodList) {
                    foreach ($tblPeriodList as $tblPeriod) {
                        if ($startDate) {
                            if ($startDate > new DateTime($tblPeriod->getFromDate())) {
                                $firstMonth = (new DateTime($tblPeriod->getFromDate()))->format('m');
                                $firstYear = (new DateTime($tblPeriod->getFromDate()))->format('Y');
                                $startDate = new DateTime($tblPeriod->getFromDate());
                            }
                        } else {
                            $firstMonth = (new DateTime($tblPeriod->getFromDate()))->format('m');
                            $firstYear = (new DateTime($tblPeriod->getFromDate()))->format('Y');
                            $startDate = new DateTime($tblPeriod->getFromDate());
                        }

                        if ($endDate) {
                            if ($endDate < new DateTime($tblPeriod->getToDate())) {
                                $endDate = new DateTime($tblPeriod->getToDate());
                            }
                        } else {
                            $endDate = new DateTime($tblPeriod->getToDate());
                        }
                    }
                }
            }

            $now = new DateTime('now');

            if (!$Month || !$Year) {
                if ($startDate <= $now && $now <= $endDate) {
                    $Month = $now->format('m');
                    $Year = $now->format('Y');
                } else {
                    $Month = $firstMonth;
                    $Year = $firstYear;
                }
            }

            $months = array(
                '01' => "Januar",
                '02' => "Februar",
                '03' => "März",
                '04' => "April",
                '05' => "Mai",
                '06' => "Juni",
                '07' => "Juli",
                '08' => "August",
                '09' => "September",
                '10' => "Oktober",
                '11' => "November",
                '12' => "Dezember"
            );

            $buttonList = array();
            if ($startDate && $endDate) {
                // SSW-1061
                $startDate = new DateTime('01.' . $startDate->format('m') . '.' . $startDate->format('Y'));
                while ($startDate <= $endDate) {
                    $startDateYear = $startDate->format('Y');
                    $startDateMonth = $startDate->format('m');
                    $buttonList[] = new Standard(
                        $startDateMonth == $Month && $startDateYear == $Year
                            ? new Info(new Bold($startDateYear . ' ' . $months[$startDateMonth]))
                            : $startDateYear . ' ' . $months[$startDateMonth],
                        '/Education/ClassRegister/Absence/Month',
                        null,
                        array(
                            'DivisionId' => $tblDivision->getId(),
                            'Month' => $startDateMonth,
                            'Year' => $startDateYear,
                            'BasicRoute' => $BasicRoute
                        )
                    );
                    $startDate->modify('+1 month');
                }
            }

            $days = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");


            $maxDays = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
            $tableHead['Number'] = '#';
            $tableHead['Name'] = 'Schüler';
            $holidays = array();
            for ($i = 1; $i <= $maxDays; $i++) {
                $date = new DateTime($i . '.' . $Month . '.' . $Year);
                if ($date->format('d.m.Y') == $now->format('d.m.Y')) {
                    $tableHead['Day' . str_pad($i, 2, '0', STR_PAD_LEFT)] = $i . '<br>' . $days[$date->format('w')]
                        . '<br>' . new \SPHERE\Common\Frontend\Link\Repository\Primary('',
                            '/Education/ClassRegister/Absence/Month/Edit', new Edit(), array(
                                'DivisionId' => $DivisionId,
                                'Month' => $Month,
                                'Year' => $Year,
                                'BasicRoute' => $BasicRoute,
                                'SelectedDate' => $date->format('d.m.Y')
                            ));
                } else {
                    $tableHead['Day' . str_pad($i, 2, '0', STR_PAD_LEFT)] = $i . '<br>' . $days[$date->format('w')]
                        . '<br>' . new Standard('', '/Education/ClassRegister/Absence/Month/Edit', new Edit(), array(
                            'DivisionId' => $DivisionId,
                            'Month' => $Month,
                            'Year' => $Year,
                            'BasicRoute' => $BasicRoute,
                            'SelectedDate' => $date->format('d.m.Y')
                        ));
                }

                if ($date->format('w') != 0 && $date->format('w') != 6) {
                    if (Term::useService()->getHolidayByDay($tblYear, $date)) {
                        $holidays[$i] = new Muted(new Small('f'));
                    } else {
                        $holidays[$i] = '';
                    }
                } else {
                    $holidays[$i] = new Muted(new Small('w'));
                }
            }

            $tableHead['ExcusedDays'] = 'E';
            $tableHead['UnexcusedDays'] = 'U';
            $tableHead['TotalDays'] = 'G';

            $studentTable = array();
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblStudentList) {
                /** @var TblPerson $tblPerson */
                foreach ($tblStudentList as $tblPerson) {
                    $studentTable[$tblPerson->getId()]['Number'] = count($studentTable) + 1;
                    $studentTable[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();
                    $countExcused = 0;
                    $countUnexcused = 0;
                    $tblAbsenceAllByPerson = Absence::useService()->getAbsenceAllByPerson($tblPerson, $tblDivision);
                    if ($tblAbsenceAllByPerson) {
                        foreach ($tblAbsenceAllByPerson as $tblAbsence) {
                            $fromDate = new DateTime($tblAbsence->getFromDate());
                            if ($tblAbsence->getToDate()) {
                                $toDate = new DateTime($tblAbsence->getToDate());
                                if ($toDate > $fromDate) {
                                    $date = $fromDate;
                                    while ($date <= $toDate) {
                                        $this->setStatusForDay($tblPerson, $tblAbsence, $tblYear, $studentTable, $date,
                                            $Month,
                                            $Year, $countExcused, $countUnexcused);
                                        $date = $date->modify('+1 day');
                                    }
                                } elseif ($toDate == $fromDate) {
                                    $this->setStatusForDay($tblPerson, $tblAbsence, $tblYear, $studentTable, $fromDate,
                                        $Month, $Year,
                                        $countExcused, $countUnexcused);
                                }
                            } else {
                                $this->setStatusForDay($tblPerson, $tblAbsence, $tblYear, $studentTable, $fromDate,
                                    $Month, $Year,
                                    $countExcused, $countUnexcused);
                            }
                        }
                    }
                    $studentTable[$tblPerson->getId()]['TotalDays'] = ($countExcused + $countUnexcused);
                    $studentTable[$tblPerson->getId()]['ExcusedDays'] = $countExcused;
                    $studentTable[$tblPerson->getId()]['UnexcusedDays'] = $countUnexcused;

                    for ($i = 1; $i <= $maxDays; $i++) {
                        if (!isset($studentTable[$tblPerson->getId()]['Day' . str_pad($i, 2, '0', STR_PAD_LEFT)])) {
                            $studentTable[$tblPerson->getId()]['Day' . str_pad($i, 2, '0',
                                STR_PAD_LEFT)] = $holidays[$i];
                        }
                    }
                }
            }

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
                                )),
                                new LayoutColumn($buttonList),
                                new LayoutColumn(array(
                                    '<br>',
                                    new Panel(
                                        'Monat',
                                        $Year . ' ' . $months[$Month],
                                        Panel::PANEL_TYPE_INFO
                                    )
                                )),
                                new LayoutColumn(array(
                                    new TableData($studentTable, null, $tableHead, false)
                                ))
                            ))
                        )),
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param TblYear $tblYear
     * @param $studentTable
     * @param DateTime $date
     * @param $month
     * @param $year
     * @param $countExcused
     * @param $countUnexcused
     */
    private function setStatusForDay(
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        TblYear $tblYear,
        &$studentTable,
        DateTime $date,
        $month,
        $year,
        &$countExcused,
        &$countUnexcused
    ) {

        if ($date->format('Y') == $year && $date->format('m') == $month) {
            if ($date->format('w') != 0 && $date->format('w') != 6) {
                if (Term::useService()->getHolidayByDay($tblYear, $date)) {
                    $studentTable[$tblPerson->getId()]['Day' . $date->format('d')] = new Muted(new Small('f'));
                } else {
                    if ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                        $countUnexcused++;
                        $studentTable[$tblPerson->getId()]['Day' . $date->format('d')]
                            = new \SPHERE\Common\Frontend\Text\Repository\Danger(new Bold('U'));
                    } elseif ($tblAbsence->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                        $countExcused++;
                        $studentTable[$tblPerson->getId()]['Day' . $date->format('d')] = new Success(new Bold('E'));
                    }
                }
            } else {
                $studentTable[$tblPerson->getId()]['Day' . $date->format('d')] = new Muted(new Small('w'));
            }
        }
    }

    /**
     * @param int $Id
     * @param bool $Confirm
     * @param string $BasicRoute
     *
     * @return Stage
     */
    public function frontendDestroyAbsence($Id = null, $Confirm = false, $BasicRoute = '')
    {

        $Stage = new Stage('Fehlzeit', 'Löschen');

        if (($tblAbsence = Absence::useService()->getAbsenceById($Id))
            && ($tblPerson = $tblAbsence->getServiceTblPerson())
            && ($tblDivision = $tblAbsence->getServiceTblDivision())
        ) {
            $Stage->addButton(new Standard(
                'Zurück', '/Education/ClassRegister/Absence', new ChevronLeft(),
                array(
                    'PersonId' => $tblPerson->getId(),
                    'DivisionId' => $tblDivision->getId(),
                    'BasicRoute' => $BasicRoute
                )
            ));

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                        new Panel(
                            'Schüler',
                            $tblPerson->getLastFirstName(),
                            Panel::PANEL_TYPE_INFO
                        ),
                        new Panel(
                            new Question() . ' Diese Fehlzeit wirklich löschen?',
                            array(
                                $tblAbsence->getFromDate()
                                . ($tblAbsence->getToDate() ? ' -  ' . $tblAbsence->getToDate() : ''),
                                ($tblAbsence->getRemark() ? ' ' . new Muted(new Small($tblAbsence->getRemark())) : null)
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Education/ClassRegister/Absence/Destroy', new Ok(),
                                array(
                                    'Id' => $Id,
                                    'Confirm' => true,
                                    'BasicRoute' => $BasicRoute
                                )
                            )
                            . new Standard(
                                'Nein', '/Education/ClassRegister/Absence', new Disable(),
                                array(
                                    'PersonId' => $tblPerson->getId(),
                                    'DivisionId' => $tblDivision->getId(),
                                    'BasicRoute' => $BasicRoute
                                )
                            )
                        ),
                    )))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Absence::useService()->destroyAbsence($tblAbsence)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success(
                                    new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Fehlzeit wurde gelöscht')
                                . new Redirect('/Education/ClassRegister/Absence', Redirect::TIMEOUT_SUCCESS,
                                    array(
                                        'PersonId' => $tblPerson->getId(),
                                        'DivisionId' => $tblDivision->getId(),
                                        'BasicRoute' => $BasicRoute
                                    )
                                )
                                : new Danger(new Ban() . ' Die Fehlzeit konnte nicht gelöscht werden')
                                . new Redirect('/Education/ClassRegister/Absence', Redirect::TIMEOUT_ERROR,
                                    array(
                                        'PersonId' => $tblPerson->getId(),
                                        'DivisionId' => $tblDivision->getId(),
                                        'BasicRoute' => $BasicRoute
                                    )
                                )
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Die Fehlzeit konnte nicht gefunden werden'),
                        new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionId
     * @param null $Month
     * @param null $Year
     * @param string $BasicRoute
     * @param null $SelectedDate
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendEditAbsenceMonth(
        $DivisionId = null,
        $Month = null,
        $Year = null,
        $BasicRoute = '',
        $SelectedDate = null,
        $Data = null
    ) {

        $Stage = new Stage('Fehlzeiten Bearbeiten', 'Monatsübersicht');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/ClassRegister/Absence/Month', new ChevronLeft(),
            array(
                'DivisionId' => $DivisionId,
                'Month' => $Month,
                'Year' => $Year,
                'BasicRoute' => $BasicRoute,
            )
        ));

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $firstMonth = null;
            $firstYear = null;

            $startDate = false;
            $endDate = false;
            $tblYear = $tblDivision->getServiceTblYear();
            if ($tblYear) {
                $tblLevel = $tblDivision->getTblLevel();
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear, $tblLevel && $tblLevel->getName() == '12');
                if ($tblPeriodList) {
                    foreach ($tblPeriodList as $tblPeriod) {
                        if ($startDate) {
                            if ($startDate > new DateTime($tblPeriod->getFromDate())) {
                                $firstMonth = (new DateTime($tblPeriod->getFromDate()))->format('m');
                                $firstYear = (new DateTime($tblPeriod->getFromDate()))->format('Y');
                                $startDate = new DateTime($tblPeriod->getFromDate());
                            }
                        } else {
                            $firstMonth = (new DateTime($tblPeriod->getFromDate()))->format('m');
                            $firstYear = (new DateTime($tblPeriod->getFromDate()))->format('Y');
                            $startDate = new DateTime($tblPeriod->getFromDate());
                        }

                        if ($endDate) {
                            if ($endDate < new DateTime($tblPeriod->getToDate())) {
                                $endDate = new DateTime($tblPeriod->getToDate());
                            }
                        } else {
                            $endDate = new DateTime($tblPeriod->getToDate());
                        }
                    }
                }
            }

            $now = new DateTime('now');

            if (!$Month || !$Year) {
                if ($startDate <= $now && $now <= $endDate) {
                    $Month = $now->format('m');
                    $Year = $now->format('Y');
                } else {
                    $Month = $firstMonth;
                    $Year = $firstYear;
                }
            }

            $months = array(
                '01' => "Januar",
                '02' => "Februar",
                '03' => "März",
                '04' => "April",
                '05' => "Mai",
                '06' => "Juni",
                '07' => "Juli",
                '08' => "August",
                '09' => "September",
                '10' => "Oktober",
                '11' => "November",
                '12' => "Dezember"
            );

            $buttonList = array();
            if ($startDate && $endDate) {
                // SSW-1061
                $startDate = new DateTime('01.' . $startDate->format('m') . '.' . $startDate->format('Y'));
                while ($startDate <= $endDate) {
                    $startDateYear = $startDate->format('Y');
                    $startDateMonth = $startDate->format('m');
                    $buttonList[] = new Standard(
                        $startDateMonth == $Month && $startDateYear == $Year
                            ? new Info(new Bold($startDateYear . ' ' . $months[$startDateMonth]))
                            : $startDateYear . ' ' . $months[$startDateMonth],
                        '/Education/ClassRegister/Absence/Month',
                        null,
                        array(
                            'DivisionId' => $tblDivision->getId(),
                            'Month' => $startDateMonth,
                            'Year' => $startDateYear,
                            'BasicRoute' => $BasicRoute
                        )
                    );
                    $startDate->modify('+1 month');
                }
            }


            if ($SelectedDate) {
                $SelectedDate = new DateTime($SelectedDate);
            }

            $days = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");

            $maxDays = cal_days_in_month(CAL_GREGORIAN, $Month, $Year);
            $tableHead['Number'] = '#';
            $tableHead['Name'] = 'Schüler';
            $holidays = array();
            for ($i = 1; $i <= $maxDays; $i++) {
                $date = new DateTime($i . '.' . $Month . '.' . $Year);
                if ($date->format('d.m.Y') == $SelectedDate->format('d.m.Y')) {
                    $tableHead['Day' . str_pad($i, 2, '0',
                        STR_PAD_LEFT)] = new \SPHERE\Common\Frontend\Message\Repository\Info(
                        $i . '<br>' . $days[$date->format('w')]
                    );
                } else {
                    $tableHead['Day' . str_pad($i, 2, '0', STR_PAD_LEFT)] = $i . '<br>' . $days[$date->format('w')];
                }
                if ($date->format('w') != 0 && $date->format('w') != 6) {
                    if (Term::useService()->getHolidayByDay($tblYear, $date)) {
                        $holidays[$i] = new Muted(new Small('f'));
                    } else {
                        $holidays[$i] = '';
                    }
                } else {
                    $holidays[$i] = new Muted(new Small('w'));
                }
            }

            $tableHead['ExcusedDays'] = 'E';
            $tableHead['UnexcusedDays'] = 'U';
            $tableHead['TotalDays'] = 'G';

            $studentTable = array();
            $tblStudentList = Division::useService()->getStudentAllByDivision($tblDivision);
            if ($tblStudentList) {
                $statusList = array(
                    TblAbsence::VALUE_STATUS_NULL => 'anwesend',
                    TblAbsence::VALUE_STATUS_EXCUSED => 'entschuldigt',
                    TblAbsence::VALUE_STATUS_UNEXCUSED => 'unentschuldigt',
                );

                $Global = $this->getGlobal();

                /** @var TblPerson $tblPerson */
                foreach ($tblStudentList as $tblPerson) {
                    $messageTimeSpan = false;

                    $studentTable[$tblPerson->getId()]['Number'] = count($studentTable) + 1;
                    $studentTable[$tblPerson->getId()]['Name'] = $tblPerson->getLastFirstName();
                    $countExcused = 0;
                    $countUnexcused = 0;
                    $tblAbsenceAllByPerson = Absence::useService()->getAbsenceAllByPerson($tblPerson, $tblDivision);
                    if ($tblAbsenceAllByPerson) {
                        foreach ($tblAbsenceAllByPerson as $tblAbsence) {
                            $fromDate = new DateTime($tblAbsence->getFromDate());
                            if ($tblAbsence->getToDate()) {
                                $toDate = new DateTime($tblAbsence->getToDate());
                                if ($toDate > $fromDate) {
                                    $date = $fromDate;
                                    while ($date <= $toDate) {

                                        if ($date == $SelectedDate) {
//                                            $Global->POST['Data'][$tblPerson->getId()] = $tblAbsence->getStatus();
                                            // SSW-403
                                            $messageTimeSpan = new Warning(new ToolTip($tblAbsence->getStatusDisplayName()
                                                . ' ' . new \SPHERE\Common\Frontend\Icon\Repository\Info(),
                                                    'Ein Fehlzeiten-Zeitraum kann nicht über die Monatsansicht bearbeitet werden.'));
                                        } else {
                                            $this->setStatusForDay($tblPerson, $tblAbsence, $tblYear, $studentTable,
                                                $date,
                                                $Month,
                                                $Year, $countExcused, $countUnexcused);
                                        }

                                        $date = $date->modify('+1 day');
                                    }
                                } elseif ($fromDate == $toDate) {
                                    if ($fromDate == $SelectedDate) {
                                        $Global->POST['Data'][$tblPerson->getId()] = $tblAbsence->getStatus();
                                    } else {
                                        $this->setStatusForDay($tblPerson, $tblAbsence, $tblYear, $studentTable, $fromDate,
                                            $Month, $Year,
                                            $countExcused, $countUnexcused);
                                    }
                                }
                            } else {
                                if ($fromDate == $SelectedDate) {
                                    $Global->POST['Data'][$tblPerson->getId()] = $tblAbsence->getStatus();
                                } else {
                                    $this->setStatusForDay($tblPerson, $tblAbsence, $tblYear, $studentTable, $fromDate,
                                        $Month, $Year,
                                        $countExcused, $countUnexcused);
                                }
                            }
                        }
                    }

                    $Global->savePost();

                    $studentTable[$tblPerson->getId()]['TotalDays'] = ($countExcused + $countUnexcused);
                    $studentTable[$tblPerson->getId()]['ExcusedDays'] = $countExcused;
                    $studentTable[$tblPerson->getId()]['UnexcusedDays'] = $countUnexcused;

                    for ($i = 1; $i <= $maxDays; $i++) {
                        if ($SelectedDate && $SelectedDate->format('d') == str_pad($i, 2, '0', STR_PAD_LEFT)) {
                            if ($messageTimeSpan) {
                                $studentTable[$tblPerson->getId()]['Day' . str_pad($i, 2, '0',
                                    STR_PAD_LEFT)] = $messageTimeSpan;
                            } else {
                                $studentTable[$tblPerson->getId()]['Day' . str_pad($i, 2, '0',
                                    STR_PAD_LEFT)] = new SelectBox('Data[' . $tblPerson->getId() . ']', '',
                                    $statusList);
                            }
                        } elseif (!isset($studentTable[$tblPerson->getId()]['Day' . str_pad($i, 2, '0',
                                STR_PAD_LEFT)])
                        ) {
                            $studentTable[$tblPerson->getId()]['Day' . str_pad($i, 2, '0',
                                STR_PAD_LEFT)] = $holidays[$i];
                        }
                    }
                }
            }

            $form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TableData($studentTable, null, $tableHead, false)
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
                                    new Panel(
                                        'Klasse',
                                        $tblDivision->getDisplayName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                )),
                                new LayoutColumn($buttonList),
                                new LayoutColumn(array(
                                    '<br>',
                                    new Panel(
                                        'Monat',
                                        $Year . ' ' . $months[$Month],
                                        Panel::PANEL_TYPE_INFO
                                    )
                                )),
                                new LayoutColumn(array(
                                    Absence::useService()->createAbsenceList($form, $tblDivision, $BasicRoute,
                                        $SelectedDate, $Data)
                                ))
                            ))
                        )),
                    )
                )
            );

            return $Stage;
        } else {
            $Stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @return Stage
     */
    public function frontendAbsenceOverview()
    {
        $Stage = new Stage('Fehlzeiten', 'Eingabe');

        // todo now
        $now = new DateTime('28.09.2020');

        $Stage->setContent(
            ApiAbsence::receiverModal()
            . new Panel(
                new Calendar() . ' Kalender' . new PullRight(
                    (new Link(
                        new PlusSign() . '  Fehlzeit hinzufügen',
                        ApiAbsence::getEndpoint(),
                        null,
                        array(),
                        false,
                        null,
                        AbstractLink::TYPE_WHITE_LINK
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal())
                ),
                ApiAbsence::receiverBlock(ApiAbsence::generateOrganizerWeekly($now->format('W') , $now->format('Y')), 'CalendarContent'),
                Panel::PANEL_TYPE_PRIMARY
            )
        );

        return $Stage;
    }
}