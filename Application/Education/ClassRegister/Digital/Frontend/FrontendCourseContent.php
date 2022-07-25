<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\Home;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class FrontendCourseContent extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/ClassRegister/Digital';

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage
     */
    public function frontendSelectCourse($DivisionId = null, $GroupId = null, string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'): Stage
    {
        $stage = new Stage('Digitales Klassenbuch', 'Kursheft auswählen');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft(), array('IsGroup' => $GroupId)
        ));

        $tblPerson = Account::useService()->getPersonByLogin();
        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblGroup = Group::useService()->getGroupById($GroupId);

        if ($tblGroup && ($tblDivisionList = $tblGroup->getCurrentDivisionList())) {
            $tblMainDivision = reset($tblDivisionList);
        } else {
            $tblMainDivision = $tblDivision;
        }

        // Klassenlehrer sieht alle Kurshefte
        if ($tblPerson && (Division::useService()->getDivisionTeacherByDivisionAndTeacher($tblMainDivision, $tblPerson))) {
            $isTeacher = false;
        } else {
            // Fachlehrer
            $isTeacher = strpos($BasicRoute, 'Teacher');
        }

        $subjectGroupList = array();
        if (($tblDivisionSubjectAllByDivision = Division::useService()->getDivisionSubjectByDivision($tblMainDivision))
            && $tblPerson
        ) {
            foreach ($tblDivisionSubjectAllByDivision as $tblDivisionSubject) {
                if (($tblSubject = $tblDivisionSubject->getServiceTblSubject())
                    && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
                    && $tblDivisionSubject->getHasGrading()
                ) {
                    // Fachlehrer benötigt einen Lehrauftrag
                    if ($isTeacher && !Division::useService()->existsSubjectTeacher($tblPerson, $tblDivisionSubject)) {
                        continue;
                    }

                    $subjectGroupList[] = array(
                        'Subject' => $tblSubject->getDisplayName(),
                        'SubjectGroup' => $tblSubjectGroup->getName(),
                        'Option' => new Standard(
                            '', self::BASE_ROUTE . '/CourseContent', new Select(),
                            array(
                                'DivisionSubjectId' => $tblDivisionSubject->getId(),
                                'GroupId' => $tblGroup ? $tblGroup->getId() : null,
                                'BasicRoute' => $BasicRoute
                            ),
                            'Auswählen'
                        )
                    );
                }
            }
        }


        $stage->setContent(
            new Layout(new LayoutGroup(array(
                Digital::useService()->getHeadLayoutRow($tblDivision ?: null, $tblGroup ?: null, $tblYear)
            )))
            . new Container('&nbsp;')
            . new TableData(
                $subjectGroupList,
                null,
                array(
                    'Subject' => 'Fach',
                    'SubjectGroup' => 'Fach-Gruppe',
                    'Option' => ''
                ),
                array(
                    'order' => array(
                        array('0', 'asc'),
                        array('1', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 2),
                        array('orderable' => false, 'width' => '1%', 'targets' => -1)
                    ),
                )
            )
        );

        return  $stage;
    }

    /**
     * @param null $DivisionSubjectId
     * @param null $GroupId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendCourseContent(
        $DivisionSubjectId = null,
        $GroupId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Kursheft');

        $tblDivision = false;
        $tblSubject = false;
        $tblSubjectGroup = false;
        if (!(($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))
            && ($tblDivision = $tblDivisionSubject->getTblDivision())
            && ($tblSubject = $tblDivisionSubject->getServiceTblSubject())
            && ($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup())
        )) {
            return new Danger('SekII-Kurs nicht gefunden', new Exclamation()) . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $DivisionId = $tblDivision->getId();
        $SubjectId = $tblSubject->getId();
        $SubjectGroupId = $tblSubjectGroup->getId();
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

        $tblYear = $tblDivision->getServiceTblYear();

        $layout = new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    (new Primary(
                        new Plus() . ' Thema/Hausaufgaben hinzufügen',
                        ApiDigital::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateCourseContentModal($DivisionId, $SubjectId, $SubjectGroupId))
                    . (new Primary(
                        new Plus() . ' Fehlzeit hinzufügen',
                        ApiAbsence::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, $DivisionId, null,
                        'DivisionSubject', $tblDivisionSubject->getId()))
                    , 8),
                new LayoutColumn(
                    new PullRight(
                        (new External(
                            'zum Notenbuch',
                            '/Education/Graduation/Gradebook/Gradebook/Teacher/Selected',
                            new Extern(),
                            array(
                                'DivisionSubjectId' => $tblDivisionSubject->getId()
                            ),
                            'Zum Notenbuch wechseln'
                        ))
                    )
                    , 4)
                ))
            )))
            . ApiDigital::receiverBlock($this->loadCourseContentTable($tblDivision, $tblSubject, $tblSubjectGroup), 'CourseContentContent');

        $stage->setContent(
            ApiDigital::receiverModal()
            . ApiAbsence::receiverModal()
            . new Layout(array(
                new LayoutGroup(array(
                    Digital::useService()->getHeadLayoutRow(null, ($tblGroup = Group::useService()->getGroupById($GroupId)) ?: null, $tblYear, $tblDivisionSubject),
                    Digital::useService()->getHeadButtonListLayoutRowForDivisionSubject($tblDivisionSubject, $DivisionId, $GroupId,
                        '/Education/ClassRegister/Digital/CourseContent', $BasicRoute)
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn($this->getStudentPanel(null, null, $tblDivisionSubject), 2),
                        new LayoutColumn($layout, 10)
                    ))
                ), new Title(new Book() . ' Kursheft'))
            ))
        );

        return $stage;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return string
     */
    public function loadCourseContentTable(TblDivision $tblDivision, TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup): string
    {
        $dataList = array();
        $divisionList = array('0' => $tblDivision);
        if (($tblCourseContentList = Digital::useService()->getCourseContentListBy($tblDivision, $tblSubject, $tblSubjectGroup))) {
            $tblDivisionSubject = Division::useService()->getDivisionSubjectByDivisionAndSubjectAndSubjectGroup(
                $tblDivision, $tblSubject, $tblSubjectGroup
            );
            foreach ($tblCourseContentList as $tblCourseContent) {
                $absenceList = array();
                $lessonArray = array();
                $lesson = $tblCourseContent->getLesson();
                $lessonArray[$lesson] = $lesson;
                if ($tblCourseContent->getIsDoubleLesson()) {
                    $lesson++;
                    $lessonArray[$lesson] = $lesson;
                }

                if (($AbsenceList = Absence::useService()->getAbsenceAllByDay(new DateTime($tblCourseContent->getDate()),
                    null, null, $divisionList, array(), $hasTypeOption, null))
                ) {
                    foreach ($AbsenceList as $Absence) {
                        if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
                            $isAdd = false;
                            if (($tblAbsenceLessonList = Absence::useService()->getAbsenceLessonAllByAbsence($tblAbsence))) {
                                foreach ($tblAbsenceLessonList as $tblAbsenceLesson) {
                                    if (isset($lessonArray[$tblAbsenceLesson->getLesson()])) {
                                        $isAdd = true;
                                        break;
                                    }
                                }
                                // ganztägig
                            } else {
                                $isAdd = true;
                            }

                            if ($isAdd) {
                                $lessonString = $tblAbsence->getLessonStringByAbsence();
                                $type = $tblAbsence->getTypeDisplayShortName();
                                $remark = $tblAbsence->getRemark();
                                $toolTip = ($lessonString ? $lessonString . ' / ' : '') . ($type ? $type . ' / ' : '') . $tblAbsence->getStatusDisplayShortName()
                                    . (($tblPersonStaff = $tblAbsence->getDisplayStaffToolTip()) ? ' - ' . $tblPersonStaff : '')
                                    . ($remark ? ' - ' . $remark : '');

                                $absenceList[] = new Container((new Link(
                                    $Absence['Person'],
                                    ApiAbsence::getEndpoint(),
                                    null,
                                    array(),
                                    $toolTip,
                                    null,
                                    $tblAbsence->getLinkType()
                                ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(),
                                    'DivisionSubject', $tblDivisionSubject ? $tblDivisionSubject->getId(): null)));
                            }
                        }
                    }
                }

                $dataList[] = array(
                    'Date' => $tblCourseContent->getDate(),
                    'Lesson' => new Center(implode(', ', $lessonArray)),
                    'IsDoubleLesson' => new Center($tblCourseContent->getIsDoubleLesson() ? 'X' : ''),
                    'Content' => $tblCourseContent->getContent(),
                    'Homework' => $tblCourseContent->getHomework(),
                    'Remark' => $tblCourseContent->getRemark(),
                    'Room' => $tblCourseContent->getRoom(),
                    'Absence' => implode(' ', $absenceList),
                    'Teacher' => $tblCourseContent->getTeacherString(),
                    'Noticed' => $tblCourseContent->getNoticedString(false),
                    'Option' =>
                        (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Edit(),
                            array(),
                            'Bearbeiten'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditCourseContentModal($tblCourseContent->getId()))
                        . (new Standard(
                            '',
                            ApiDigital::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteCourseContentModal($tblCourseContent->getId()))
                );
            }
        }

        return new TableData(
            $dataList,
            null,
            array(
                'Date' => 'Datum',
                'Lesson' => new ToolTip('UE', 'Unterrichtseinheit'),
                'IsDoubleLesson' => 'Doppel&shy;stunde',
                'Room' => 'Raum',
                'Content' => 'Thema',
                'Homework' => 'Hausaufgaben',
                'Remark' => 'Bemerkungen',
                'Absence' => 'Fehlzeiten',
                'Teacher' => 'Lehrer',
                'Noticed' => 'Kenntnis genommen (SL)',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array(0, 'desc')
                ),
                'columnDefs' => array(
                    array('type' => 'de_date', 'targets' => 0),
                    array('width' => '50px', 'targets' => 0),
                    array('width' => '25px', 'targets' => 1),
                    array('width' => '25px', 'targets' => 2),
                    array('width' => '25px', 'targets' => 3),
                    array('width' => '50px', 'targets' => 8),
                    array('width' => '60px', 'targets' => -1),
                ),
                'responsive' => false,
                'paging' => false,
                'info' => false,
                'searching' => false,
            )
        );
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblSubjectGroup $tblSubjectGroup
     * @param null $CourseContentId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formCourseContent(TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup,
        $CourseContentId = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $CourseContentId
            && ($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblCourseContent->getDate();
            $Global->POST['Data']['Lesson'] = $tblCourseContent->getLesson();
            $Global->POST['Data']['serviceTblPerson'] =
                ($tblPerson = $tblCourseContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblCourseContent->getContent();
            $Global->POST['Data']['Homework'] = $tblCourseContent->getHomework();
            $Global->POST['Data']['Remark'] = $tblCourseContent->getRemark();
            $Global->POST['Data']['Room'] = $tblCourseContent->getRoom();
            $Global->POST['Data']['IsDoubleLesson'] = $tblCourseContent->getIsDoubleLesson() ? 1 : 0;

            $Global->savePost();
        }

        if ($CourseContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditCourseContentSave($CourseContentId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateCourseContentSave(
                    $tblDivision->getId(),
                    $tblSubject->getId(),
                    $tblSubjectGroup->getId()
                ));
        }
        $buttonList[] = $saveButton;

        for ($i = 0; $i < 11; $i++) {
            $lessons[] = new SelectBoxItem($i, $i . '. Unterrichtseinheit');
        }

        // Unterrichteinheit löchen
        if ($CourseContentId) {
            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger(
                'Löschen',
                ApiDigital::getEndpoint(),
                new Remove(),
                array(),
                false
            ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenDeleteCourseContentModal($CourseContentId));
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired()
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[Lesson]', 'Unterrichtseinheit', array('{{ Name }}' => $lessons)))->setRequired()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new CheckBox('Data[IsDoubleLesson]', 'Doppelstunde', 1)
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Content]', 'Thema', 'Thema', new Edit())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Homework]', 'Hausaufgaben', 'Hausaufgaben', new Home())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Remark]', 'Bemerkungen', 'Bemerkungen', new Comment())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextField('Data[Room]', 'Raum', 'Raum', new MapMarker())
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        $buttonList
                    )
                )),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     * @param TblDivisionSubject|null $tblDivisionSubject
     *
     * @return string
     */
    public function getStudentPanel(?TblDivision $tblDivision, ?TblGroup $tblGroup, ?TblDivisionSubject $tblDivisionSubject): string
    {
        $tblPersonList = false;
        $dataList = array();
        if ($tblDivision) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        } elseif ($tblGroup) {
            if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringNaturalOrderSorter());
            }
        } elseif ($tblDivisionSubject) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
        }

        if ($tblPersonList) {
            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $dataList[] = new PullLeft(++$count) . new PullRight($tblPerson->getLastFirstName());
            }
        }

        return new Panel(
            'Schüler',
            $dataList,
            Panel::PANEL_TYPE_INFO
        );
    }
}