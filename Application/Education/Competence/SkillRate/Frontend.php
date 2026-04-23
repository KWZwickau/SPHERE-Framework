<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /** @noinspection PhpUnused */
    public function frontendSkillRateSelect($SelectedYearId = null, $SchoolTypeId = null): Stage
    {
//        $stage = new Stage('Kompetenzbewertung', 'Auswählen');
        $stage = new Stage();
        $title = "<h3>Kompetenzbewertung <small>Auswählen</small></h3>";

        $hasHeadmasterRole = Access::useService()->hasAuthorization('/Education/Competence/SkillRate/Headmaster');
        $hasTeacherRole = Access::useService()->hasAuthorization('/Education/Competence/SkillRate/Teacher');
        $hasAllReadonlyRole = Access::useService()->hasAuthorization('/Education/Competence/SkillRate/AllReadOnly');

        // bei Schulleitung auch die Schulart in DB speichern, damit man es nicht jedes Mal neu auswählen muss
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($SchoolTypeId !== null) {
            if ($tblConsumer) {
                ConsumerSetting::useService()->createAccountSetting('SkillRateHeadmasterSelectSchoolTypeByConsumerId_' . $tblConsumer->getId(), $SchoolTypeId);
            }
        } elseif ($tblConsumer
            && ($value = ConsumerSetting::useService()->getAccountSettingValue('SkillRateHeadmasterSelectSchoolTypeByConsumerId_' . $tblConsumer->getId()))
        ) {
            $SchoolTypeId = $value;
        }

        if (($roleValue = SkillRate::useService()->getRole())) {
            if ($roleValue == "Headmaster") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsHeadmaster"] = 1;
                $global->savePost();
            }
            if ($roleValue == "AllReadonly") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsAllReadonly"] = 1;
                $global->savePost();
            }
        }

        if ($roleValue == 'Teacher' && !$hasTeacherRole) {
            if ($hasHeadmasterRole) {
                ConsumerSetting::useService()->createAccountSetting('SkillRateRole', 'Headmaster');
            } elseif ($hasAllReadonlyRole) {
                ConsumerSetting::useService()->createAccountSetting('SkillRateRole', 'AllReadonly');
            }
        }

        $global = $this->getGlobal();
        $global->POST["Data"]["SelectedYearId"] = $SelectedYearId ?: -1;
        $global->savePost();

        $tblYearList = Term::useService()->getYearAll();
        $tblCurrentYears = new TblYear();
        $tblCurrentYears->setName('Aktuelles Schuljahr');
        $tblCurrentYears->setId(-1);
        $tblYearList[] = $tblCurrentYears;

        $formColumns = [];
        if ($hasHeadmasterRole && $hasTeacherRole) {
            $formColumns[] = new FormColumn(new PullRight(
                (new CheckBox('Data[IsHeadmaster]', new Bold('Schulleitung'), 1))
                    ->ajaxPipelineOnChange(array(ApiSkillRate::pipelineChangeYearOrRole($SchoolTypeId)))
            ), 6);
        } elseif ($hasAllReadonlyRole && $hasTeacherRole) {
            $formColumns[] = new FormColumn(new PullRight(
                (new CheckBox('Data[IsAllReadonly]', new Bold('Alle Kompetenzbewertungen'), 1))
                    ->ajaxPipelineOnChange(array(ApiSkillRate::pipelineChangeYearOrRole($SchoolTypeId)))
            ), 6);
        }

        $formColumns[] = new FormColumn(
            (new SelectBox('Data[SelectedYearId]', '', array("{{ DisplayName }}" => $tblYearList)))
                ->ajaxPipelineOnChange(array(ApiSkillRate::pipelineChangeYearOrRole($SchoolTypeId)))
            , 6);
        $form = (new Form(new FormGroup(new FormRow($formColumns))))->disableSubmitAction();

        $stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $title
                    , 8),
                    new LayoutColumn(
                        (new Container($form))->setStyle(['margin-top: 10px;'])
                    , 4)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiSkillRate::receiverBlock($this->loadViewSelect($SelectedYearId, $SchoolTypeId), 'Content')
                    )
                ))
            )))
        );

        return $stage;
    }

    /**
     * @param null $SelectedYearId
     * @param null $SchoolTypeId
     * @param bool|null $DontShowDivisionTeacher
     *
     * @return string
     */
    public function loadViewSelect($SelectedYearId = null, $SchoolTypeId = null, ?bool $DontShowDivisionTeacher = null): string
    {
        $role = SkillRate::useService()->getRole();

        $isTeacher = $role == "Teacher";
        $form = null;
        if ($SelectedYearId && $SelectedYearId > 0 && ($tblYear = Term::useService()->getYearById($SelectedYearId))) {
            $tblYearList = [$tblYear];
        } else {
            $tblYearList = Term::useService()->getYearByNow();
        }

        if ($tblYearList) {
            // Lehrer
            if ($isTeacher) {
                if ($DontShowDivisionTeacher === null) {
                    $ShowDivisionTeacher = !ConsumerSetting::useService()->getAccountSettingValue("DontShowDivisionTeacherSkillRates");
                } else {
                    $ShowDivisionTeacher = !$DontShowDivisionTeacher;
                }
                $content = $this->getSelectTeacher($tblYearList, $SelectedYearId, $ShowDivisionTeacher);
                if ($ShowDivisionTeacher) {
                    $global = $this->getGlobal();
                    $global->POST['Data']['ShowDivisionTeacher'] = 1;
                    $global->savePost();
                }

                $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[ShowDivisionTeacher]', new Bold('Kompetenzbewertung inklusive Kursleiter anzeigen'), 1))
                        ->ajaxPipelineOnChange(array(ApiSkillRate::pipelineChangeShowDivisionTeacher($SelectedYearId)))
                )))))->disableSubmitAction();

                // Schulleitung, Integrationsbeauftragte
            } else {
                $content = $this->getSelectHeadmaster($SelectedYearId, $SchoolTypeId);
            }
        } else {
            $content = new Danger("Schuljahr nicht gefunden", new Exclamation());
        }

        return ($form ?: '')
            . $content;
    }

    /**
     * @param $SelectedYearId
     * @param $SchoolTypeId
     *
     * @return string
     */
    private function getSelectHeadmaster($SelectedYearId = null, $SchoolTypeId = null): string
    {
        $content = '';
        if (($tblSchoolTypeList = SkillGrid::useService()->getAvailableSchoolTypeList())) {
            $route = "/Education/Competence/SkillRate";
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                $params = [
                    'SelectedYearId' => $SelectedYearId,
                    'SchoolTypeId' => $tblSchoolType->getId()
                ];
                $name = $tblSchoolType->getName() . ($tblSchoolType->getShortName() == 'Gy' ||  $tblSchoolType->getShortName() == 'BGy' ? ' (SekI)' : '');
                if ($tblSchoolType->getId() == $SchoolTypeId) {
                    $content .= new Standard(new Info(new Bold($name)), $route, new Edit(), $params);
                } else {
                    $content .= new Standard($name, $route, null, $params);
                }
            }
        }
        $content .= new Container('&nbsp;');

        if ($SelectedYearId && $SelectedYearId > 0 && ($tblYear = Term::useService()->getYearById($SelectedYearId))) {
            $tblYearList = [$tblYear];
        } else {
            $tblYearList = Term::useService()->getYearByNow();
        }

        if ($SchoolTypeId
            && ($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))
            && $tblYearList
        ) {
            $dataList = array();
            foreach ($tblYearList as $tblYear) {
                if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if (!($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                            || !isset($tblSchoolTypeList[$tblSchoolType->getId()])
                        ) {
                            continue;
                        }

                        // SekII ignorieren
                        if ($tblDivisionCourse->getType()->getIsCourseSystem()
                            || (($tblSchoolType->getShortName() == 'Gy'  || $tblSchoolType->getShortName() == 'BGy')
                                && DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse))
                        ) {
                            continue;
                        }

                        $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, $SelectedYearId);
                    }
                }
            }

            // bei der DataTable dürfen als Key nur Zahlen verwenden
            $dataList = array_values($dataList);
            $content .= $this->getTable($dataList);

        } else {
            $content .= new Warning("Bitte wählen Sie zunächst eine Schulart aus.", new Exclamation());
        }

        return $content;
    }

    /**
     * @param $tblYearList
     * @param $SelectedYearId
     * @param bool $showDivisionTeacher
     *
     * @return string
     */
    private function getSelectTeacher($tblYearList, $SelectedYearId, bool $showDivisionTeacher): string
    {
        if (($tblPersonLogin = Account::useService()->getPersonByLogin())) {
            $divisionCourseSubjectList = array();
            $dataList = array();
            foreach ($tblYearList as $tblYear) {
                if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPersonLogin))) {
                    // Lehraufträge
                    foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                        $this->setTeacherLectureshipSelectData($dataList, $tblTeacherLectureship, $divisionCourseSubjectList, $SelectedYearId);
                    }

                    // Klassenlehrer aus den Lehraufträgen der Lehrer
                    if ($showDivisionTeacher
                        && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByDivisionTeacher($tblPersonLogin, $tblYear, true))
                    ) {
                        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                            if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)) {
                                // für SekII gibt es keine Kompetenzbewertung
//                                if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByStudentDivisionCourseAndPeriod($tblDivisionCourse,
//                                    1))) {
//                                    foreach ($tblStudentSubjectList as $tblStudentSubject) {
//                                        if (($tblDivisionCourseSubject = $tblStudentSubject->getTblDivisionCourse())
//                                            && !isset($divisionCourseSubjectList[$tblDivisionCourseSubject->getId()])
//                                        ) {
//                                            $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourseSubject, $tblYear, $tblPersonLogin,
//                                                $divisionCourseSubjectList);
//                                        }
//                                    }
//                                }
                            } else {
                                // SekI
                                $this->setDivisionCourseSelectDataList($dataList, $tblDivisionCourse, $tblYear, $SelectedYearId, $tblPersonLogin, $divisionCourseSubjectList);
                            }
                        }
                    }
                }
            }
            // bei der DataTable dürfen als Key nur Zahlen verwenden
            $dataList = array_values($dataList);
            $content = $this->getTable($dataList);
        } else {
            $content = new Warning("Keine Lehraufträge vorhanden", new Exclamation());
        }

        return $content;
    }

    /**
     * @param array $dataList
     * @param TblTeacherLectureship $tblTeacherLectureship
     * @param $divisionCourseSubjectList
     * @param $SelectedYearId
     */
    private function setTeacherLectureshipSelectData(
        array &$dataList, TblTeacherLectureship $tblTeacherLectureship, &$divisionCourseSubjectList, $SelectedYearId): void
    {
        if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
            && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
        ) {
            // SekII ignorieren
            if ($tblDivisionCourse->getType()->getIsCourseSystem()
                || DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
            ) {
                return;
            }

            // prüfen, ob ein Schüler überhaupt das Fach mit Benotung hat nicht bei Schulleitung aus Performance gründen
            if (is_array($divisionCourseSubjectList) && !isset($divisionCourseSubjectList[$tblDivisionCourse->getId()])) {
                $divisionCourseSubjectList[$tblDivisionCourse->getId()] = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse);
            }
            if ($divisionCourseSubjectList == null
                || isset($divisionCourseSubjectList[$tblDivisionCourse->getId()][$tblSubject->getId()])
                // Mandanten-Einstellung: Bei Kopfnotenaufträgen können auch Kopfnoten für Fächer vergeben werden, welche nicht benotet werden
                // erstmal nicht Berücksichtigen
//                || (($tblSetting = ConsumerSetting::useService()->getSetting(
//                        'Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'
//                    ))
//                    && $tblSetting->getValue()
//                    && Grade::useService()->getBehaviorTaskListByDivisionCourse($tblDivisionCourse)
//                )
            ) {
                $key = $tblDivisionCourse->getId() . '_' . $tblSubject->getId();
                if (!isset($dataList[$key])) {
                    $dataList[$key] = array(
                        'Year' => $tblTeacherLectureship->getYearName(),
                        'DivisionCourse' => $tblTeacherLectureship->getCourseName(),
                        'CourseType' => $tblDivisionCourse->getTypeName(),
                        'Subject' => $tblTeacherLectureship->getSubjectName(),
                        'SubjectTeachers' => $tblTeacherLectureship->getSubjectTeachers(),
                        'Option' => (new Standard("", "/Education/Competence/SkillRate/DivisionCourse", new Check(),
                            ['SelectedYearId' => $SelectedYearId, 'DivisionCourseId' => $tblDivisionCourse->getId(), 'SubjectId' => $tblSubject->getId()], "Auswählen"))
                    );
                }
            }
        }
    }

    /**
     * @param array $dataList
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblYear $tblYear
     * @param $SelectedYearId
     * @param TblPerson|null $tblPerson
     * @param null $divisionCourseSubjectList
     *
     * @return void
     */
    private function setDivisionCourseSelectDataList(
        array &$dataList,
        TblDivisionCourse $tblDivisionCourse,
        TblYear $tblYear,
        $SelectedYearId,
        ?TblPerson $tblPerson = null,
        $divisionCourseSubjectList = null,
    ): void {
        // Lerngruppe
        if (($tblSubject = $tblDivisionCourse->getServiceTblSubject())
            && $tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP
        ) {
            $this->setDivisionCourseSelectData($dataList, $tblDivisionCourse, $tblSubject, $tblYear);
            // alle Lehraufträge des Kurses
        } elseif (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, $tblDivisionCourse))) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                // eigene Lehraufträge bei Klassenlehrern ignorieren
                if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson()) && $tblPerson
                    && $tblTeacher->getId() == $tblPerson->getId()
                ) {
                    continue;
                }

                $this->setTeacherLectureshipSelectData($dataList, $tblTeacherLectureship, $divisionCourseSubjectList, $SelectedYearId);
            }
        }
    }

    /**
     * @param array $dataList
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param $SelectedYearId
     */
    private function setDivisionCourseSelectData(array &$dataList, TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, $SelectedYearId): void
    {
        $key = $tblDivisionCourse->getId() . '_' . $tblSubject->getId();
        if (!isset($dataList[$key])) {
            $dataList[$key] = array(
                'Year' => $tblDivisionCourse->getYearName(),
                'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                'CourseType' => $tblDivisionCourse->getTypeName(),
                'Subject' => $tblSubject->getDisplayName(),
                'SubjectTeachers' => $tblDivisionCourse->getDivisionTeacherNameListString(', '),
                'Option' => (new Standard("", "/Education/Competence/SkillRate/DivisionCourse", new Check(),
                    ['SelectedYearId' => $SelectedYearId, 'DivisionCourseId' => $tblDivisionCourse->getId(), 'SubjectId' => $tblSubject->getId()], "Auswählen"))
            );
        }
    }

    /**
     * @param array $dataList
     *
     * @return TableData
     */
    private function getTable(array $dataList): TableData
    {
        return new TableData(
            $dataList,
            null,
            array(
                'Year' => 'Schuljahr',
                'DivisionCourse' => 'Kurs',
                'CourseType' => 'Kurs-Typ',
                'Subject' => 'Fach',
                'SubjectTeachers' => 'Fachlehrer',
                'Option' => ''
            ),
            array(
                'order' => array(
                    array('0', 'desc'),
                    array('1', 'asc'),
                    array('3', 'asc'),
                ),
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => 1),
                    array('orderable' => false, 'width' => '30px', 'targets' => -1),
                    array('searchable' => false, 'targets' => -1),
                )
            )
        );
    }

    /** @noinspection PhpUnused */
    public function frontendDivisionCourse($SelectedYearId = null, $DivisionCourseId = null, $SubjectId = null): Stage
    {
        $textCourse = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
        ) {
            $textCourse = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
        }

        $stage = new Stage();
        $stage->setContent(
            new Title(
                new Standard("Zurück", "/Education/Competence/SkillRate", new ChevronLeft(), ['SelectedYearId' => $SelectedYearId])
                . "&nbsp;&nbsp;&nbsp;Kompetenzbewertung"
                . new Muted(new Small(" für Kurs: ")) . $textCourse
                . new Muted(new Small(" im Fach: ")) . $textSubject
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $this->loadDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId)
        );

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $SelectedYearId
     * @param bool $ShowInActive
     *
     * @return string
     */
    public function loadDivisionCourseContent($DivisionCourseId, $SubjectId, $SelectedYearId, bool $ShowInActive = false): string
    {
        $role = SkillRate::useService()->getRole();
        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $SubjectId, $role);
        $isCheckTeacherLectureship = $isEdit && ($role == 'Teacher');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $inactiveStudentList = array();
            if ($ShowInActive) {
                $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(true, true, new DateTime('today'));
                if (($tblDivisionCourseMemberList = $tblDivisionCourse->getStudentsWithSubCourses(true, false))) {
                    /** @var TblDivisionCourseMember $tblDivisionCourseMember */
                    foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                        if ($tblDivisionCourseMember->isInActive() && ($tblPersonTemp = $tblDivisionCourseMember->getServiceTblPerson())) {
                            $inactiveStudentList[$tblPersonTemp->getId()] = $tblPersonTemp;
                        }
                    }
                }
            } else {
                $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(false, true, new DateTime('today'));
                if (($countInActive = $tblDivisionCourse->getCountInActiveStudents())) {
                    $tempContent = $countInActive == 1 ? ' inaktiven' : ' inaktive';
                    $optionInActive = (new CheckBox('Data[OptionInActive]', $countInActive . $tempContent . ' Schüler mit anzeigen', 1));
                    // Todo inactive anzeigen
//                        ->ajaxPipelineOnChange(ApiGradeBook::pipelineCheckInActive($DivisionCourseId, $SubjectId, $Filter));
                }
            }

            $gradeFrontend = Grade::useFrontend();

            $integrationList = array();
            $pictureList = array();
            $courseList = array();
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject, isset($inactiveStudentList[$tblPerson->getId()])
                        ))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
                        // Schüler-Informationen
                        Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);

                        // Todo Kompetenzbereiche
                    }
                }
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $gradeFrontend->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            $headerList['Option'] = $gradeFrontend->getTableColumnHead('&nbsp;');

            $count = 0;
            $bodyList = [];
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                            $tblPerson, $tblYear, $tblSubject, isset($inactiveStudentList[$tblPerson->getId()])
                        ))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
                        $bodyList[$tblPerson->getId()] = $gradeFrontend->getGradeBookPreBodyList($tblPerson, ++$count,
                            $hasPicture, $hasIntegration, $hasCourse,
                            $pictureList, $integrationList, $courseList, isset($inactiveStudentList[$tblPerson->getId()]));

                        $bodyList[$tblPerson->getId()]['Option'] = $gradeFrontend->getTableColumnBody(
                            new Standard('', '/Education/Competence/SkillRate/Student', new EyeOpen(), [
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'SubjectId' => $tblSubject->getId(),
                                'PersonId' => $tblPerson->getId(),
                                'SelectedYearId' => $SelectedYearId
                            ])
                        );
                    }
                }
            }

            return $gradeFrontend->getTableCustom($headerList, $bodyList);
        }

        return "";
    }

    /** @noinspection PhpUnused */
    public function frontendStudent($DivisionCourseId, $SubjectId, $PersonId, $SelectedYearId = null): Stage
    {
        $stage = new Stage('Kompetenzbewertung', 'Schüleransicht');
        $stage->addButton(new Standard("Zurück", "/Education/Competence/SkillRate/DivisionCourse", new ChevronLeft(),
            ['DivisionCourseId' => $DivisionCourseId, 'SubjectId' => $SubjectId, 'SelectedYearId' => $SelectedYearId]));

        $error = '';
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $error = new Danger('Kurs wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            $error = new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            $error = new Danger('Fach wurde nicht gefunden!', new Exclamation());
        }

        if ($error) {
            $stage->setContent($error);
        } else {
            $stage->setContent(
                $this->getStudentHead($tblPerson, $tblDivisionCourse, $tblSubject ?: null)
                . ApiSkillRate::receiverBlock($this->loadStudentContent($DivisionCourseId, $SubjectId, $PersonId, $SelectedYearId), 'Content')
            );
        }

        return $stage;
    }

    /** @noinspection PhpUnused */
    public function frontendEditStudent(): Stage
    {
        $stage = new Stage('Kompetenzbewertung', 'Übersicht');

        $stage->setContent(ApiSkillRate::receiverBlock(
            $this->loadStudentEdit(972, 39, 9),
            'EditStudentSkillRateContent'
        ));

        return $stage;
    }

    public function loadStudentContent($DivisionCourseId, $SubjectId, $PersonId, $SelectedYearId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return new Danger('Fach wurde nicht gefunden!', new Exclamation());
        }

        // Todo individuelle Kompetenzen hinzufügen
        $dataList = [];
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
            foreach ($tblSkillList as $tblSkill) {
                if (($tblSkillArea = $tblSkill->getTblSkillArea())) {
                    // todo fett falls schon bewertet
                    if (!isset($dataList[$tblSkillArea->getId()])) {
                        $dataList[$tblSkillArea->getId()] = [
                            'name' => $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich',
                            'skills' => []
                        ];
                    }

                    $dataList[$tblSkillArea->getId()]['skills'][] =
                        ($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                            . $tblSkill->getSkill();
                }
            }
        }

        $content = '';
        foreach ($dataList as $item) {
            // bei alten Schuljahren grau statt blau
            $content .= new Panel($item['name'], $item['skills'], Panel::PANEL_TYPE_INFO);
        }

        return $content;
    }

    public function loadStudentEdit($PersonId, $DivisionCourseId, $SubjectId): string
    {
        if (!($tblPerson = Person::useService()->getPersonById($PersonId))) {
            return new Danger('Schüler wurde nicht gefunden!', new Exclamation());
        }
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Kurs nicht gefunden.', new Exclamation());
        }
        if (!($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            return new Danger('Schuljahr nicht gefunden.', new Exclamation());
        }

        $tblSubject = $SubjectId ? Subject::useService()->getSubjectById($SubjectId) : null;

        return $this->getStudentHead($tblPerson, $tblDivisionCourse, $tblSubject ?: null)
            . new Well($this->formStudentSkillRateList($tblPerson, $tblYear, $tblSubject ?: null));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return string
     */
    private function getStudentHead(TblPerson $tblPerson, TblDivisionCourse $tblDivisionCourse, ?TblSubject $tblSubject): string
    {
        $pictureHeight = '138px';
        $panelStudent = new Panel(
            'Schüler',
            $tblPerson->getLastFirstNameWithCallNameUnderline(true),
            Panel::PANEL_TYPE_INFO
        );
        $list[] = $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName();
        if ($tblSubject) {
            $pictureHeight = '170px';
            $list[] = "Fach: {$tblSubject->getDisplayName()}";
        }
        $panelCourse = new Panel(
            'Kurs',
            $list,
            Panel::PANEL_TYPE_INFO
        );

        if (($tblPersonPicture = Storage::useService()->getPersonPictureByPerson($tblPerson))) {
            $PersonPicture = (new Link($tblPersonPicture->getPicture($pictureHeight, '10px'), $tblPerson->getId()))
                ->ajaxPipelineOnClick(ApiPersonPicture::pipelineShowPersonPicture($tblPerson->getId()));
        } else {
            $File = FileSystem::getFileLoader('/Common/Style/Resource/SSWIcon.png');
            $PersonPicture = '<img src="' . $File->getLocation() . '" style="height: ' . $pictureHeight . '; border-radius: 10px; opacity: 0.2">';
        }

        $rows[] = new LayoutRow(array(
            new LayoutColumn(new Layout(new LayoutGroup(array(
                new LayoutRow(new LayoutColumn($panelStudent)),
                new LayoutRow(new LayoutColumn($panelCourse)),
            ))), 10),
            new LayoutColumn(new Center($PersonPicture), 2),
        ));

        return ApiPersonPicture::receiverModal() . new Layout(new LayoutGroup($rows));
    }

    public function formStudentSkillRateList(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, $ErrorList = null): Form
    {
        // Todo individuelle Kompetenzen hinzufügen
        // Todo anzeige geänderter Skillname
        $dataList = [];
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($level = $tblStudentEducation->getLevel()) !== null
        ) {
            $scoreTypeListBySkillGrid = [];
            $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear);
            $tblSkillList = SkillGrid::useService()->getSkillListBy($tblSchoolType, $level, $tblSubject);
            foreach ($tblSkillList as $tblSkill) {
                if (($tblSkillArea = $tblSkill->getTblSkillArea())) {
                    if (!isset($dataList[$tblSkillArea->getId()])) {
                        $dataList[$tblSkillArea->getId()] = [
                           'name' => $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich',
                            'isBold' => false,
                            'skills' => []
                        ];
                    }

                    // Eingabe entsprechend dem Bewertungssystem
                    $tblSkillGrid = $tblSkill->getTblSkillGrid();
                    if ($tblSkillGrid && !isset($scoreTypeListBySkillGrid[$tblSkillGrid->getId()])) {
                        if (($tblScoreType = $tblSkillGrid->getServiceTblScoreType())) {
                            $scoreTypeListBySkillGrid[$tblSkillGrid->getId()] = [
                                'tblScoreTypeId' => $tblScoreType->getId(),
                                'Items' => $tblScoreType->getScoreTypeItems()
                            ];
                        } else {
                            $scoreTypeListBySkillGrid[$tblSkillGrid->getId()] = null;
                        }
                    }
                    if ($tblSkillGrid && isset($scoreTypeListBySkillGrid[$tblSkillGrid->getId()])
                        && ($scoreType = $scoreTypeListBySkillGrid[$tblSkillGrid->getId()])
                    ) {
                        // individuelles Bewertungssystem
                        $identifier = "Data[ScoreTypeSkills][{$scoreType['tblScoreTypeId']}][{$tblSkill->getId()}]";
                        $input = new SelectBox($identifier, '', ['{{ Name }}' => $scoreType['Items']], null, true, null);
                    } else {
                        // Prozent
                        $identifier = "Data[PercentSkills][{$tblSkill->getId()}]";
                        $input = new TextField($identifier);
                    }

                    // Anzeige Fehlermeldung
                    if (isset($ErrorList[$identifier])) {
                        $input->setError($ErrorList[$identifier]['Message']);
                    }

                    $isBold = false;
                    $displayLast = '';
                    if (isset($tblStudentSkillList[$tblSkill->getId()])
                        && ($displayLast = SkillRate::useService()->getDisplayStudentSkillRateLastOrAverage($tblPerson, $tblStudentSkillList[$tblSkill->getId()]))
                    ) {
                        $dataList[$tblSkillArea->getId()]['isBold'] = true;
                        $isBold = true;
                    }

                    $displaySkill = ($tblSkill->getLevel() ? new Muted($tblSkill->getLevel() . ' ') : '')
                        . ($isBold ? new Bold($tblSkill->getSkill()) : $tblSkill->getSkill())
                        . ($displayLast ? new PullRight($displayLast) : '');

                    $dataList[$tblSkillArea->getId()]['skills'][] = new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn((new Container($displaySkill))->setStyle(['padding-top: 5px;']), 10),
//                        new LayoutColumn(((new Container($displayLast)))->setStyle(['padding-top: 5px;']), 1),
                        new LayoutColumn($input, 2)
                    ))));
                }
            }
        }

        $rows = [];
        $rows[] = new FormRow(array(
            new FormColumn((new DatePicker('Data[Date]', '', 'Datum', new Calendar()))->setRequired(), 3),
            new FormColumn((new TextField('Data[Comment]',
                'Wie erfolgte die Feststellung der Kompetenz (z.B.: HA, Stundenaufgabe, Arbeitsblatt usw.)',
                'Öffentlicher Kommentar zur Kompetenzfeststellung')), 9)
        ));
        foreach ($dataList as $item) {
            $rows[] = new FormRow(new FormColumn(new Panel(
                $item['isBold'] ? new Bold($item['name']) : $item['name'],
                $item['skills'],
                Panel::PANEL_TYPE_INFO
            )));
        }

        if ($ErrorList) {
            $rows[] = new FormRow(new FormColumn(new Danger("Die Daten wurden nicht gespeichert. Bitte beachten Sie die Fehlermeldungen weiter oben.")));
        }

        $rows[] = new FormRow(array(
            new FormColumn(array(
                new Container('&nbsp;'),
                (new Primary('Speichern', ApiSkillRate::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiSkillRate::pipelineSaveEditStudentSkillRate($tblPerson->getId(), $tblYear->getId(), $tblSubject?->getId())),
                new Standard('Abbrechen', '/Education/Competence/SkillRate', new Disable())
            ))
        ));

        $form = (new Form(new FormGroup($rows)))->disableSubmitAction();

        if ($ErrorList) {
            foreach ($ErrorList as $error) {
                $form->setError($error['Name'], $error['Message']);
            }
        }

        return $form;
    }
}