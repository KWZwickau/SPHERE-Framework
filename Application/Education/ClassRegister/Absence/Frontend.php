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
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
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
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\IMessageInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
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
     * @param string $ReturnRoute
     * @param null $GroupId
     * @param null $DivisionSubjectId
     *
     * @return string
     */
    public function frontendAbsenceStudent($DivisionId = null, $PersonId = null, string $BasicRoute = '', string $ReturnRoute = '',
        $GroupId = null, $DivisionSubjectId = null) : string
    {
        $Stage = new Stage('Digitales Klassenbuch', 'Fehlzeiten Übersicht des Schülers');
        if ($ReturnRoute) {
            $Stage->addButton(new Standard('Zurück', $ReturnRoute, new ChevronLeft(),
                    array(
                        'DivisionSubjectId' => $DivisionSubjectId,
                        'DivisionId' => $GroupId ? null : $DivisionId,
                        'GroupId'    => $GroupId,
                        'BasicRoute' => $BasicRoute,
                    ))
            );
        }

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblPerson = Person::useService()->getPersonById($PersonId);
        if ($tblPerson && $tblDivision) {
            $Stage->setContent(
                new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel(
                                        'Schüler',
                                        $tblPerson->getLastFirstNameWithCallNameUnderline(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ), 6),
                                new LayoutColumn(array(
                                    new Panel(
                                        'Klasse, Schulart',
                                        $tblDivision->getDisplayName() . ', ' . $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_INFO
                                    )
                                ), 6)
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    ApiAbsence::receiverModal()
                                    . (new PrimaryLink(
                                        new Plus() . ' Fehlzeit hinzufügen',
                                        ApiAbsence::getEndpoint()
                                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal($PersonId, $DivisionId)),
                                    new Container('&nbsp;')
                                ))
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    ApiAbsence::receiverBlock(
                                        $this->loadAbsenceTable($tblPerson, $tblDivision),
                                        'AbsenceContent'
                                    )
                                ))
                            ))
                        )) //, new Title(new ListingTable() . ' Übersicht')),
                    )
                )
            );

            return $Stage;
        } else {

            return $Stage . new Danger('Person nicht gefunden.', new Ban());
        }
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendAbsenceMonth(
        $DivisionId = null,
        $GroupId = null,
        $DivisionSubjectId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Fehlzeiten (Kalenderansicht)');

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            if ($GroupId) {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'GroupId' => $GroupId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            } else {
                $stage->addButton(new Standard(
                    'Zurück', '/Education/ClassRegister/Digital/SelectCourse', new ChevronLeft(), array(
                        'DivisionId' => $DivisionId,
                        'BasicRoute' => $BasicRoute
                    )
                ));
            }
        } else {
            $stage->addButton(new Standard(
                'Zurück', $BasicRoute, new ChevronLeft()
            ));
        }
        if ($tblDivision) {
            $currentDate = new DateTime('now');
            // wenn der aktuelle Tag im Schuljahr ist dann diesen Anzeigen, ansonsten erster Tag des Schuljahres
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDate && $endDate
                    && ($currentDate < $startDate || $currentDate > $endDate)
                ) {
                    $currentDate = $startDate;
                }
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow(
                            $tblDivision , null, $tblYear, $tblDivisionSubject ?: null
                        ),
                        $tblDivisionSubject
                            ? Digital::useService()->getHeadButtonListLayoutRowForDivisionSubject($tblDivisionSubject, $DivisionId, $GroupId,
                                '/Education/ClassRegister/Digital/AbsenceMonth', $BasicRoute)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivision, null,
                                '/Education/ClassRegister/Digital/AbsenceMonth', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        ApiAbsence::receiverModal()
                        . ApiAbsence::receiverBlock(
                            Consumer::useService()->getAccountSettingValue('AbsenceView') == 'Month'
                                ? ApiAbsence::generateOrganizerMonthly($tblDivision->getId(), $currentDate->format('m'), $currentDate->format('Y'))
                                : ApiAbsence::generateOrganizerForDivisionWeekly($tblDivision->getId(), $currentDate->format('W'), $currentDate->format('Y')),
                            'CalendarContent'
                        )
                    )), new Title(new Calendar() . ' Fehlzeiten (Kalenderansicht)'))
                ))
            );
        } else {
            return new Danger('Klasse nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param null $AbsenceId
     * @param bool $hasSearch
     * @param string $Search
     * @param null $Data
     * @param null $PersonId
     * @param null $DivisionId
     * @param IMessageInterface|null $messageSearch
     * @param IMessageInterface|null $messageLesson
     * @param null $Date
     * @param null $Type
     * @param null $TypeId
     *
     * @return Form
     */
    public function formAbsence(
        $AbsenceId = null,
        $hasSearch = false,
        $Search = '',
        $Data = null,
        $PersonId = null,
        $DivisionId = null,
        IMessageInterface $messageSearch = null,
        IMessageInterface $messageLesson = null,
        $Date = null,
        $Type = null,
        $TypeId = null
    ) {
        if ($Data === null && $AbsenceId === null) {
            $isFullDay = true;

            $global = $this->getGlobal();
            $global->POST['Data']['IsFullDay'] = $isFullDay;

            if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'DefaultStatusForNewAbsence'))) {
                $status = $tblSetting->getValue();
            } else {
                $status = TblAbsence::VALUE_STATUS_UNEXCUSED;
            }
            $global->POST['Data']['Status'] = $status;

            $global->POST['Data']['IsCertificateRelevant'] = true;
            if ($Date) {
                $global->POST['Data']['FromDate'] = $Date;
            }

            $global->savePost();
        } elseif ($Data === null && $AbsenceId && ($tblAbsence = Absence::useService()->getAbsenceById($AbsenceId))) {
            $global = $this->getGlobal();
            if(($lessons = Absence::useService()->getLessonAllByAbsence($tblAbsence))) {
                $isFullDay = false;
                foreach($lessons as $lesson) {
                    $global->POST['Data']['UE'][$lesson] = 1;
                }
            } else {
                $isFullDay = true;
            }

            $global->POST['Data']['IsFullDay'] = $isFullDay;
            $global->POST['Data']['FromDate'] = $tblAbsence->getFromDate();
            $global->POST['Data']['ToDate'] = $tblAbsence->getToDate();
            $global->POST['Data']['Remark'] = $tblAbsence->getRemark();
            $global->POST['Data']['Type'] = $tblAbsence->getType();
            $global->POST['Data']['Status'] = $tblAbsence->getStatus();
            $global->POST['Data']['IsCertificateRelevant'] = $tblAbsence->getIsCertificateRelevant();

            $global->savePost();
        } else {
            $isFullDay = isset($Data['IsFullDay']) ? $Data['IsFullDay'] : false;
        }

        if ($AbsenceId) {
            $saveButton = (new PrimaryLink('Speichern', ApiAbsence::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAbsence::pipelineEditAbsenceSave($AbsenceId));
        } else {
            $saveButton = (new PrimaryLink('Speichern', ApiAbsence::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiAbsence::pipelineCreateAbsenceSave($PersonId, $DivisionId, $hasSearch, $Type, $TypeId));
        }

        $formRows = array();
        if ($Type && $TypeId) {
            $tblPersonList = false;
            switch ($Type) {
                case 'Division':
                    if (($tblDivision = Division::useService()->getDivisionById($TypeId))) {
                        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                    }
                    break;
                case 'Group':
                    if (($tblGroup = Group::useService()->getGroupById($TypeId))) {
                        $tblPersonList = $tblGroup->getStudentOnlyList();
                    }
                    break;
                case 'DivisionSubject':
                    if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($TypeId))) {
                        $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
                    }
                    break;
            }

            if ($tblPersonList) {
                $formRows[] = new FormRow(new FormColumn(
                    (new SelectBox('Data[PersonId]', 'Schüler', array('{{ LastFirstName }}' => $tblPersonList)))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiAbsence::pipelineLoadType())
                ));
            }
        } elseif ($hasSearch) {
            $formRows[] = new FormRow(array(
                new FormColumn(array(
                    new Panel(
                        'Schüler',
                        (new TextField(
                            'Search',
                            '',
                            'Suche',
                            new Search()
                        ))->ajaxPipelineOnKeyUp(ApiAbsence::pipelineSearchPerson())
                        . ApiAbsence::receiverBlock($this->loadPersonSearch($Search, $messageSearch), 'SearchPerson')
                        , Panel::PANEL_TYPE_INFO
                    )
                ))
            ));
        }

        $formRows[] = new FormRow(array(
            new FormColumn(
                new DatePicker('Data[FromDate]', '', 'Datum von', new Calendar()), 6
            ),
            new FormColumn(
                new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 6
            ),
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(array(
                (new CheckBox('Data[IsFullDay]', 'ganztägig', 1))->ajaxPipelineOnClick(ApiAbsence::pipelineLoadLesson()),
                ApiAbsence::receiverBlock($this->loadLesson($isFullDay, $messageLesson), 'loadLesson')
            ))
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                ApiAbsence::receiverBlock($this->loadType($PersonId, $DivisionId), 'loadType')
            )
        ));
        $formRows[] = new FormRow(array(
            new FormColumn(
                new TextField('Data[Remark]', '', 'Bemerkung'), 12
            ),
        ));
        $formRows[] = new FormRow(array(
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
        ));
        $formRows[] = new FormRow(array(
           new FormColumn(
               new CheckBox('Data[IsCertificateRelevant]', 'zeugnisrelevant', 1)
           )
        ));

        $buttons = array();
        $buttons[] = $saveButton;
        if ($AbsenceId) {
            $buttons[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
                'Löschen',
                ApiAbsence::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenDeleteAbsenceModal($AbsenceId));
        }

        $formRows[] = new FormRow(array(
            new FormColumn($buttons)
        ));

        return (new Form(new FormGroup(
            $formRows
        )))->disableSubmitAction();
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
                $result = new Warning('Es wurden keine entsprechenden Schüler gefunden.', new Ban());
            }
        } else {
            $result =  new Warning('Bitte geben Sie mindestens 3 Zeichen in die Suche ein.', new Exclamation());
        }

        return $result . ($message ? $message : '');
    }

    /**
     * @param $IsFullDay
     * @param IMessageInterface $message
     *
     * @return Layout|null
     */
    public function loadLesson($IsFullDay, IMessageInterface $message = null)
    {
        if ($IsFullDay) {
            if ($message === null) {
                return null;
            } else {
                return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($message))));
            }

        } else {
            $left = array();
            $right = array();
            for ($i = 0; $i < 7; $i++) {
                $left[] = $this->setCheckBoxLesson($i);
                if ($i < 6) {
                    $right[] = $this->setCheckBoxLesson($i + 7);
                }
            }

            return new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn($left, 6),
                    new LayoutColumn($right, 6)
                )),
                new LayoutRow(array(
                    new LayoutColumn($message)
                )),
            )));
        }
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return SelectBox|null
     */
    public function loadType($PersonId = null, $DivisionId = null)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivision = $DivisionId === null
                ? Student::useService()->getCurrentMainDivisionByPerson($tblPerson)
                : Division::useService()->getDivisionById($DivisionId)
            )
            && Absence::useService()->hasAbsenceTypeOptions($tblDivision)
        ) {
            $global = $this->getGlobal();
            $global->POST['Data']['Type'] = TblAbsence::VALUE_TYPE_THEORY;
            $global->savePost();

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
     * @return Stage
     */
    public function frontendAbsenceOverview()
    {
        $Stage = new Stage('Fehlzeiten', 'Eingabe');

        $now = new DateTime('now');

        $Stage->setContent(
            (new PrimaryLink(
                'Fehlzeit hinzufügen',
                ApiAbsence::getEndpoint(),
                new PlusSign()
            ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal())
            . new Container('&nbsp;')
            . ApiAbsence::receiverModal()
            . new Panel(
                new Calendar() . ' Kalender',
                ApiAbsence::receiverBlock(ApiAbsence::generateOrganizerWeekly($now->format('W') , $now->format('Y')), 'CalendarWeekContent'),
                Panel::PANEL_TYPE_PRIMARY
            )
        );

        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     *
     * @return TableData
     */
    public function loadAbsenceTable(TblPerson $tblPerson, TblDivision $tblDivision)
    {
        $hasAbsenceTypeOptions = Absence::useService()->hasAbsenceTypeOptions($tblDivision);
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

                $isOnlineAbsence = $tblAbsence->getIsOnlineAbsence();

                $item = array(
                    'FromDate' => $isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getFromDate() . '</span>' : $tblAbsence->getFromDate(),
                    'ToDate' => $isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getToDate() . '</span>' : $tblAbsence->getToDate(),
                    'Days' => ($days = $tblAbsence->getDays(
                        null,
                        $count,
                        ($tblCompany = $tblDivision->getServiceTblCompany()) ? $tblCompany : null)) == 1
                        ? $days . ' ' . new Small(new Muted($tblAbsence->getWeekDay()))
                        : $days,
                    'Lessons' => $tblAbsence->getLessonStringByAbsence(),
                    'Remark' => $tblAbsence->getRemark(),
                    'Status' => $status,
                    'IsCertificateRelevant' => $tblAbsence->getIsCertificateRelevant() ? 'ja' : 'nein',
                    'PersonCreator' => $tblAbsence->getDisplayPersonCreator(false),
                    'PersonStaff' => $tblAbsence->getDisplayStaff(),
                    'Option' =>
                        (new Standard(
                            '',
                            ApiAbsence::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId()))
                        . (new Standard(
                            '',
                            ApiAbsence::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenDeleteAbsenceModal($tblAbsence->getId()))
                );

                if ($hasAbsenceTypeOptions) {
                    $item['Type'] = $tblAbsence->getTypeDisplayName();
                }

                $tableData[] = $item;
            }
        }

        if ($hasAbsenceTypeOptions) {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Type' => 'Typ',
                'Remark' => 'Bemerkung',
                'PersonCreator' => 'Ersteller',
                'PersonStaff' => 'Bearbeiter',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
                'Option' => ''
            );
        } else {
            $columns = array(
                'FromDate' => 'Datum von',
                'ToDate' => 'Datum bis',
                'Days' => 'Tage',
                'Lessons' => 'Unterrichts&shy;einheiten',
                'Remark' => 'Bemerkung',
                'PersonCreator' => 'Ersteller',
                'PersonStaff' => 'Bearbeiter',
                'IsCertificateRelevant' => 'Zeugnisrelevant',
                'Status' => 'Status',
                'Option' => ''
            );
        }
        // name Downloadfile
        $FileName = 'Fehlzeiten '.$tblPerson->getLastName().' '.$tblPerson->getFirstName().' '.(new DateTime())->format('d-m-Y');

        return new TableData(
            $tableData,
            null,
            $columns,
            array(
                'order' => array(
                    array(0, 'desc')
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('type' => 'de_date', 'targets' => 1),
                    array('orderable' => false, 'width' => '60px', 'targets' => -1)
                ),
                'responsive' => false,
//                'ExtensionColVisibility' => array('Enabled' => true),
                'ExtensionDownloadExcel' => array(
                    'Enabled' => true,
                    'FileName' => $FileName,
                    'Columns' => '0,1,2,3,4,5,6,7,8',
                )
            )
        );
    }
}