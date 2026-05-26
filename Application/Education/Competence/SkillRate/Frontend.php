<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use SPHERE\Application\Api\Education\Competence\ApiSkillRate;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Consumer as ConsumerSetting;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;

class Frontend extends FrontendStudent
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
        $route = "/Education/Competence/SkillRate";
        if (($tblSchoolTypeList = SkillGrid::useService()->getAvailableSchoolTypeList())) {
            // bei nur einer Schulart, diese vorauswählen
            if (count($tblSchoolTypeList) == 1) {
                $SchoolTypeId = $SchoolTypeId ?: current($tblSchoolTypeList)->getId();
            }

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
    protected function getTable(array $dataList): TableData
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
                ),
                'responsive' => false,
                'destroy' => true
            )
        );
    }
}