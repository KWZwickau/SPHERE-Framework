<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Api\Education\ClassRegister\ApiInstructionSetting;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
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
use SPHERE\Common\Frontend\Icon\Repository\Check;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class FrontendCourseContent extends Extension implements IFrontendInterface
{
    const BASE_ROUTE = '/Education/ClassRegister/Digital';

    /**
     * @param null $DivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage
     */
    public function frontendSelectCourse($DivisionCourseId = null, string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'): Stage
    {
        $stage = new Stage('Digitales Klassenbuch', 'Kursheft auswählen');

        $stage->addButton(new Standard(
            'Zurück', $BasicRoute, new ChevronLeft()
        ));

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {

            $tblPerson = Account::useService()->getPersonByLogin();

            // Klassenlehrer/Tudor sieht alle Kurshefte
            if ($tblPerson
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson)
            ) {
                $isTeacher = false;
            } else {
                // Fachlehrer
                $isTeacher = strpos($BasicRoute, 'Teacher');
            }

            $dataList = array();
            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourse, 1))
                && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            ) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblDivisionCourseSubject = $tblStudentSubject->getTblDivisionCourse())
                        && !isset($dataList[$tblDivisionCourseSubject->getId()])
                        && ($tblSubject = $tblStudentSubject->getServiceTblSubject())
                    ) {
                        // Fachlehrer benötigt einen Lehrauftrag
                        if ($isTeacher) {
                            if (!$tblPerson || !DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, $tblDivisionCourseSubject, $tblSubject)) {
                                continue;
                            }
                        }

                        $dataList[$tblDivisionCourseSubject->getId()] = array(
                            'Subject' => $tblSubject->getDisplayName(),
                            'DivisionCourse' => $tblStudentSubject->getIsAdvancedCourse()
                                ? new Bold($tblDivisionCourseSubject->getDisplayName())
                                : $tblDivisionCourseSubject->getDisplayName(),
                            'Option' => new Standard(
                                '', self::BASE_ROUTE . '/CourseContent', new Select(),
                                array(
                                    'DivisionCourseId' => $tblDivisionCourseSubject->getId(),
                                    'BackDivisionCourseId' => $DivisionCourseId,
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
                    Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                    Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/SelectCourse', $BasicRoute)
                )))
                . new Container('&nbsp;')
                . new TableData(
                    $dataList,
                    null,
                    array(
                        'Subject' => 'Fach',
                        'DivisionCourse' => 'Kurs',
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
        } else {
            $stage->setContent(
                new Danger('Kurs wurde nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR)
            );
        }

        return  $stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendCourseContent(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Kursheft');

        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('SekII-Kurs nicht gefunden', new Exclamation()) . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $DivisionCourseId = $tblDivisionCourse->getId();
        $stage->addButton(Digital::useFrontend()->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

        $layout = new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    (new Primary(
                        new Plus() . ' Thema/Hausaufgaben hinzufügen',
                        ApiDigital::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenCreateCourseContentModal($DivisionCourseId))
                    . (new Primary(
                        new Plus() . ' Fehlzeit hinzufügen',
                        ApiAbsence::getEndpoint()
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenCreateAbsenceModal(null, $DivisionCourseId))
                    , 8),
                new LayoutColumn(
                    new PullRight(
                        (new External(
                            'zum Notenbuch',
                            '/Education/Graduation/Grade/GradeBook',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'SubjectId' => ($tblSubject = $tblDivisionCourse->getServiceTblSubject()) ? $tblSubject->getId() : null,
                                'IsDirectJump' => true
                            ),
                            'Zum Notenbuch wechseln'
                        ))
                    )
                    , 4)
                ))
            )))
            . ApiDigital::receiverBlock($this->loadCourseContentTable($tblDivisionCourse), 'CourseContentContent');

        $stage->setContent(
            ApiDigital::receiverModal()
            . ApiAbsence::receiverModal()
            . new Layout(array(
                new LayoutGroup(array(
                    Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                    Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/CourseContent',
                        $BasicRoute, $BackDivisionCourseId)
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            ApiDigital::receiverBlock($this->loadCourseMissingStudentContent($tblDivisionCourse), 'CourseMissingStudentContent')
                            . $this->getStudentPanel($tblDivisionCourse)
                            , 2),
                        new LayoutColumn($layout, 10)
                    ))
                ), new Title(new Book() . ' Kursheft'))
            ))
        );

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $IsControl
     *
     * @return string
     */
    public function loadCourseContentTable(TblDivisionCourse $tblDivisionCourse, bool $IsControl = false): string
    {
        $dataList = array();
        $hasTypeOption = false;
        $divisionCourseList = array('0' => $tblDivisionCourse);
        if (($tblCourseContentList = Digital::useService()->getCourseContentListBy($tblDivisionCourse))) {
            foreach ($tblCourseContentList as $tblCourseContent) {
                $absenceList = array();
                $lessonArray = array();
                $lesson = $tblCourseContent->getLesson();
                $lessonArray[$lesson] = $lesson;
                if ($tblCourseContent->getIsDoubleLesson()) {
                    $lesson++;
                    $lessonArray[$lesson] = $lesson;
                }

                if (($AbsenceList = Absence::useService()->getAbsenceAllByDay(new DateTime($tblCourseContent->getDate()), null, null, $divisionCourseList, $hasTypeOption, null))) {
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
                                ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $tblDivisionCourse ? $tblDivisionCourse->getId() : null)));
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
                        $IsControl
                            ? ''
                            : (new Standard(
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

        if ($IsControl)  {
            $columns = array(
                'Date' => 'Datum',
                'Lesson' => new ToolTip('UE', 'Unterrichtseinheit'),
                'IsDoubleLesson' => 'Doppel&shy;stunde',
                'Room' => 'Raum',
                'Content' => 'Thema',
                'Homework' => 'Hausaufgaben',
                'Remark' => 'Bemerkungen',
                'Absence' => 'Fehlzeiten',
                'Teacher' => 'Lehrer',
                'Noticed' => 'Kenntnis genommen (SL)'
            );
        } else {
            $columns = array(
                'Date' => 'Datum',
                'Lesson' => new ToolTip('UE', 'Unterrichtseinheit'),
                'IsDoubleLesson' => 'Doppel&shy;stunde',
                'Room' => 'Raum',
                'Content' => 'Thema',
                'Homework' => 'Hausaufgaben',
                'Remark' => 'Bemerkungen',
                'Absence' => 'Fehlzeiten',
                'Teacher' => 'Lehrer',
                'Option' => ''
            );
        }

        return new TableData(
            $dataList,
            null,
            $columns,
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
//                    array('width' => '60px', 'targets' => -1),
                ),
                'responsive' => false,
                'paging' => false,
                'info' => false,
                'searching' => false,
            )
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $CourseContentId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formCourseContent(TblDivisionCourse $tblDivisionCourse, $CourseContentId = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $CourseContentId
            && ($tblCourseContent = Digital::useService()->getCourseContentById($CourseContentId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblCourseContent->getDate();
            $Global->POST['Data']['Lesson'] = $tblCourseContent->getLesson() === 0 ? -1 : $tblCourseContent->getLesson();
            $Global->POST['Data']['serviceTblPerson'] =
                ($tblPerson = $tblCourseContent->getServiceTblPerson()) ? $tblPerson->getId() : 0;
            $Global->POST['Data']['Content'] = $tblCourseContent->getContent();
            $Global->POST['Data']['Homework'] = $tblCourseContent->getHomework();
            $Global->POST['Data']['Remark'] = $tblCourseContent->getRemark();
            $Global->POST['Data']['Room'] = $tblCourseContent->getRoom();
            $Global->POST['Data']['IsDoubleLesson'] = $tblCourseContent->getIsDoubleLesson() ? 1 : 0;

            $Global->savePost();
        }

        if ($setPost && !$CourseContentId) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = (new DateTime('today'))->format('d.m.Y');
            $Global->savePost();
        }

        if ($CourseContentId) {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineEditCourseContentSave($CourseContentId));
        } else {
            $saveButton = (new Primary('Speichern', ApiDigital::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiDigital::pipelineCreateCourseContentSave($tblDivisionCourse->getId()));
        }
        $buttonList[] = $saveButton;

        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'StartsLessonContentWithZeroLesson'))
            && $tblSetting->getValue()
        ) {
            $minLesson = 0;
        } else {
            $minLesson = 1;
        }
        for ($i = 0; $i < 13; $i++) {
            $lessons[] = new SelectBoxItem($i, $i . '. Unterrichtseinheit');
        }
        if ($minLesson == 0) {
            $lessons[] = new SelectBoxItem(-1, '0. Unterrichtseinheit');
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
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function getStudentPanel(TblDivisionCourse $tblDivisionCourse): string
    {
        $dataList = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $dataList[] = new PullLeft(++$count) . new PullRight($tblPerson->getLastFirstNameWithCallNameUnderline());
            }
        }

        return new Panel(
            'Schüler',
            $dataList,
            Panel::PANEL_TYPE_INFO
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function loadCourseMissingStudentContent(TblDivisionCourse $tblDivisionCourse): string
    {
        $hasTypeOption = false;
        $absenceList = array();
        $divisionCourseList = array('0' => $tblDivisionCourse);
        if (($AbsenceList = Absence::useService()->getAbsenceAllByDay(new DateTime('today'), null, null, $divisionCourseList, $hasTypeOption, null))) {
            foreach ($AbsenceList as $Absence) {
                if (($tblAbsence = Absence::useService()->getAbsenceById($Absence['AbsenceId']))) {
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
                    ))->ajaxPipelineOnClick(ApiAbsence::pipelineOpenEditAbsenceModal($tblAbsence->getId(), $tblDivisionCourse->getId())));
                }
            }
        }

        if ($absenceList) {
            return new Panel(
                'Heute fehlende Schüler',
                $absenceList,
                Panel::PANEL_TYPE_WARNING
            );
        } else {
            return '';
        }
    }

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     *
     * @return Stage|string
     */
    public function frontendCourseControl(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher'
    ) {
        $stage = new Stage('Digitales Klassenbuch', 'Kontrolle');

        if (!(($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId)))) {
            return new Danger('SekII-Kurs nicht gefunden', new Exclamation()) . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        $DivisionCourseId = $tblDivisionCourse->getId();
        $stage->addButton(Digital::useFrontend()->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

        $stage->setContent(
            ApiDigital::receiverModal()
            . ApiAbsence::receiverModal()
            . new Layout(array(
                new LayoutGroup(array(
                    Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                    Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/CourseControl',
                        $BasicRoute, $BackDivisionCourseId)
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            (new Primary(
                                new Check() . ' Kenntnis genommen (SL)',
                                ApiInstructionSetting::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiInstructionSetting::pipelineSaveHeadmasterNoticed($DivisionCourseId))
                            . ApiDigital::receiverBlock($this->loadCourseContentTable($tblDivisionCourse, true), 'CourseContentContent')
                        )
                    ))
                ), new Title(new Book() . ' Kursheft Kontrolle'))
            ))
        );

        return $stage;
    }
}