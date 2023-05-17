<?php

namespace SPHERE\Application\Transfer\Education;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger as DangerButton;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class Frontend extends FrontendStudentCourse
{
    private array $tabList = array(
        0 => 'Klassen',
        1 => 'Lehrer',
        2 => 'Fächer',
        3 => 'Zusammenfassung / Endgültiger Import'
    );

    /**
     * @param TblImport $tblImport
     * @param string $Tab
     * @param null $Data
     *
     * @return string
     */
    public function getLectureshipContent(TblImport $tblImport, string $Tab, $Data = null): string
    {
        // nächsten Tab ermitteln
        $index = array_search($Tab, $this->tabList);
        $NextTab = $this->tabList[$index + 1] ?? '';

        switch ($Tab) {
            case 'Klassen': $content = $this->getDivisionContent($tblImport, $NextTab, $Data); break;
            case 'Lehrer': $content = $this->getTeacherContent($tblImport, $NextTab, $Data); break;
            case 'Fächer': $content = $this->getSubjectContent($tblImport, $NextTab, $Data); break;
            case 'Zusammenfassung / Endgültiger Import': $content = $this->getImportPreviewContent($tblImport, $Data); break;
            default: $content = '';
        }

        return (new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    $this->getButtonList($tblImport, $Tab, $this->tabList)
                )
            )),
            new LayoutRow(array(
                new LayoutColumn(
                    new Container('&nbsp;') . $content
                )
            ))
        ))));
    }

    /**
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return string
     */
    private function getDivisionContent(TblImport $tblImport, string $NextTab, $Data): string
    {
        $rows = array();
        $divisionNameList = array();
        if (($tblYear = $tblImport->getServiceTblYear())
            && ($tblImportLectureshipList = $tblImport->getImportLectureships())
        ) {
            $tblDivisionCourseList = array();
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = $tblDivisionCourseListDivision;
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }
            if (($tblDivisionCourseListTeachingGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_TEACHING_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListTeachingGroup);
            }

            $global = $this->getGlobal();
            $tblImportLectureshipList = $this->getSorter($tblImportLectureshipList)->sortObjectBy('DivisionName', new StringNaturalOrderSorter());
            /** @var TblImportLectureship $tblImportLectureship */
            foreach ($tblImportLectureshipList as $tblImportLectureship) {
                if (($divisionName = $tblImportLectureship->getDivisionName())
                    && !isset($divisionNameList[$divisionName])
                ) {
                    $divisionNameList[$divisionName] = $divisionName;

                    // Mapping
                    if (($tblDivisionCourse = Education::useService()->getImportMappingValueBy(
                        TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $divisionName, $tblYear
                    ))) {
                        $status = new Warning(new Bold('Mapping'));
                    // Found
                    } elseif (($tblDivisionCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($divisionName, $tblYear))) {
                        $status = new Success(new SuccessIcon());
                    // Missing
                    } else {
                        $status = new Danger(new Ban());
                    }

                    // POST setzen
                    if ($tblDivisionCourse) {
                        $global->POST['Data'][$tblImportLectureship->getId()] = $tblDivisionCourse->getId();
                        $global->savePost();
                    }

                    $select = new SelectBox('Data[' . $tblImportLectureship->getId() . ']', '', array('{{ TypeName }}: {{ Name }}' => $tblDivisionCourseList));
                    $rows[] = new LayoutRow(array(
                        new LayoutColumn($divisionName, self::LEFT),
                        new LayoutColumn($status, self::MIDDLE),
                        new LayoutColumn($select, self::RIGHT)
                    ));
                }
            }
        }

        if (empty($rows)) {
            return new WarningMessage('Es wurden keine Klassen im Import gefunden. Somit können auch keine Lehraufträge importiert werden', new Exclamation());
        } else {
            // Kopf erstellen
            $header = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Bold('Klassen in ' . $tblImport->getExternSoftwareName()), self::LEFT),
                new LayoutColumn(new Bold('Status'), self::MIDDLE),
                new LayoutColumn(new Bold('Auswahl Kurs in der Schulsoftware'), self::RIGHT)
            ))));

            $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup($rows))
            )))))->appendFormButton(new Primary('Speichern und Weiter', new Save()));

            return new Title($header)
                . new Well(Education::useService()->saveMappingDivisionCourse($form, $tblImport, $NextTab, $Data, $tblYear));
        }
    }

    /**
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return string
     */
    public function getTeacherContent(TblImport $tblImport, string $NextTab, $Data): string
    {
        $rows = array();
        $personList[0] = '-[ Nicht ausgewählt ]-';
        if (($tblPersonListTeacher = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('TEACHER')))) {
            foreach ($tblPersonListTeacher as $tblPersonTeacher) {
                if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPersonTeacher))
                    && $tblTeacher->getAcronym()
                ) {
                    $acronym = $tblTeacher->getAcronym();
                } else {
                    $acronym = '-NA-';
                }

                $personList[$tblPersonTeacher->getId()] = $acronym . ' - ' . $tblPersonTeacher->getFullName();
            }
        }
        $teacherAcronymList = array();
        if (($tblImportLectureshipList = $tblImport->getImportLectureships())) {
            $global = $this->getGlobal();
            $tblImportLectureshipList = $this->getSorter($tblImportLectureshipList)->sortObjectBy('TeacherAcronym', new StringNaturalOrderSorter());
            /** @var TblImportLectureship $tblImportLectureship */
            foreach ($tblImportLectureshipList as $tblImportLectureship) {
                if (($teacherAcronym = $tblImportLectureship->getTeacherAcronym())
                    && !isset($teacherAcronymList[$teacherAcronym])
                ) {
                    $teacherAcronymList[$teacherAcronym] = $teacherAcronym;

                    // Mapping
                    /** @var TblPerson $tblPerson */
                    if (($tblPerson = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $teacherAcronym))) {
                        $tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson);
                        $status = new Warning(new Bold('Mapping'));
                    // Found
                    } elseif (($tblTeacher = Teacher::useService()->getTeacherByAcronym($teacherAcronym))
                        && ($tblPerson = $tblTeacher->getServiceTblPerson())
                    ) {
                        $status = new Success(new SuccessIcon());
                    // Missing
                    } else {
                        $status = new Danger(new Ban());
                    }

                    // Lehrer ist nicht mehr in der Gruppe Lehrer
                    if ($tblPerson && !isset($personList[$tblPerson->getId()])) {
                        if ($tblTeacher && $tblTeacher->getAcronym()) {
                            $acronym = $tblTeacher->getAcronym();
                        } else {
                            $acronym = '-NA-';
                        }

                        $personList[$tblPerson->getId()] = $acronym . ' - ' . $tblPerson->getFullName();
                    }


                    // POST setzen
                    if ($tblPerson) {
                        $global->POST['Data'][$tblImportLectureship->getId()] = $tblPerson->getId();
                        $global->savePost();
                    }

                    $select = new SelectBox('Data[' . $tblImportLectureship->getId() . ']', '', $personList);
                    $rows[] = new LayoutRow(array(
                        new LayoutColumn($teacherAcronym, self::LEFT),
                        new LayoutColumn($status, self::MIDDLE),
                        new LayoutColumn($select, self::RIGHT)
                    ));
                }
            }
        }

        if (empty($rows)) {
            return new WarningMessage('Es wurden keine Lehrer-Kürzel im Import gefunden. Somit können auch keine Lehraufträge importiert werden', new Exclamation());
        } else {
            // Kopf erstellen
            $header = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Bold('Lehrer-Kürzel in ' . $tblImport->getExternSoftwareName()), self::LEFT),
                new LayoutColumn(new Bold('Status'), self::MIDDLE),
                new LayoutColumn(new Bold('Auswahl Lehrer in der Schulsoftware'), self::RIGHT)
            ))));

            $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup($rows))
            )))))->appendFormButton(new Primary('Speichern und Weiter', new Save()));

            return new Title($header)
                . new Well(Education::useService()->saveMappingTeacher($form, $tblImport, $NextTab, $Data));
        }
    }

    /**
     * @param TblImport $tblImport
     * @param $Data
     *
     * @return string
     */
    private function getImportPreviewContent(TblImport $tblImport, $Data): string
    {
        list(
            $importDivisionCourseList, $missingDivisionCourseList,
            $importTeacherList, $missingTeacherList,
            $importSubjectList, $missingSubjectList,
            $createTeacherLectureshipList, $existsTeacherLectureshipList, $deleteTeacherLectureshipList
        ) = $this->getImportLectureshipPreviewData($tblImport, true);

        // sortieren
        sort($importDivisionCourseList, SORT_NATURAL);
        sort($missingDivisionCourseList, SORT_NATURAL);
        sort($importTeacherList);
        sort($missingTeacherList);
        sort($importSubjectList);
        sort($missingSubjectList);
        sort($deleteTeacherLectureshipList, SORT_NATURAL);

        return new Layout(array(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Klassen werden importiert', implode(', ', $importDivisionCourseList), Panel::PANEL_TYPE_SUCCESS)
                    , 8),
                    new LayoutColumn(
                        $missingDivisionCourseList
                            ? new Panel('Klassen können nicht importiert werden', implode(', ', $missingDivisionCourseList), Panel::PANEL_TYPE_DANGER)
                            : ''
                    , 4)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Lehrer werden importiert', implode(', ', $importTeacherList), Panel::PANEL_TYPE_SUCCESS)
                        , 8),
                    new LayoutColumn(
                        $missingTeacherList
                            ? new Panel('Lehrer können nicht importiert werden', implode(', ', $missingTeacherList), Panel::PANEL_TYPE_DANGER)
                            : ''
                    , 4)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Fächer werden importiert', implode(', ', $importSubjectList), Panel::PANEL_TYPE_SUCCESS)
                    , 8),
                    new LayoutColumn(
                        $missingSubjectList
                            ? new Panel('Fächer können nicht importiert werden', implode(', ', $missingSubjectList), Panel::PANEL_TYPE_DANGER)
                            : ''
                    , 4)
                )),
            )),
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        $createTeacherLectureshipList
                            ? new SuccessMessage('Es werden ' . count($createTeacherLectureshipList) . ' Lehraufträge neu angelegt.', new Plus())
                            : new WarningMessage('Es werden keine neuen Lehraufträge angelegt', new Ban())
                    )
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $existsTeacherLectureshipList
                            ? new WarningMessage('Es existieren bereits ' . count($existsTeacherLectureshipList) . ' Lehraufträge in der Schulsoftware.')
                            : ''
                    )
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $deleteTeacherLectureshipList
                            ? new Panel(
                                new Minus() . '  Es werden ' . count($deleteTeacherLectureshipList) . ' Lehraufträge gelöscht!',
                                $deleteTeacherLectureshipList,
                                Panel::PANEL_TYPE_DANGER
                            )
                            : ''
                    )
                )),
            ), new Title('Lehraufträge'))
        ))
            . Education::useService()->saveLectureshipFromImport(
                new Form(new FormGroup(new FormRow(array(
                    new FormColumn(new HiddenField('Data[Id]')),
                    new FormColumn(
                        new DangerButton('Import unwiderruflich Durchführen', new Save())
                    )
                )))),
                $tblImport,
                $Data
            );
    }

    /**
     * @param TblImport $tblImport
     * @param bool $IsPreview
     *
     * @return array
     */
    public function getImportLectureshipPreviewData(TblImport $tblImport, bool $IsPreview): array
    {
        $divisionNameList = array();
        $importDivisionCourseList = array();
        $missingDivisionCourseList = array();

        $teacherAcronymList = array();
        $importTeacherList = array();
        $missingTeacherList = array();

        $subjectAcronymList = array();
        $importSubjectList = array();
        $missingSubjectList = array();

        $previewCreateTeacherLectureshipList = array();
        $existsTeacherLectureshipList = array();
        $previewDeleteTeacherLectureshipList = array();

        $saveCreateTeacherLectureshipList = array();
        $saveDeleteTeacherLectureshipList = array();

        if (($tblYear = $tblImport->getServiceTblYear())
            && ($tblImportLectureshipList = $tblImport->getImportLectureships())
        ) {
            $tblImportLectureshipList = $this->getSorter($tblImportLectureshipList)->sortObjectBy('DivisionName', new StringNaturalOrderSorter());
            /** @var TblImportLectureship $tblImportLectureship */
            foreach ($tblImportLectureshipList as $tblImportLectureship) {
                if (($divisionName = $tblImportLectureship->getDivisionName())
                    && ($teacherAcronym = $tblImportLectureship->getTeacherAcronym())
                    && ($subjectAcronym = $tblImportLectureship->getSubjectAcronym())
                ) {
                    // Klassen
                    if (!isset($divisionNameList[$divisionName])) {
                        // Mapping
                        if (($tblDivisionCourse = Education::useService()->getImportMappingValueBy(
                            TblImportMapping::TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME, $divisionName, $tblYear
                        ))) {
                            // Found
                        } else {
                            $tblDivisionCourse = Education::useService()->getDivisionCourseByDivisionNameAndYear($divisionName, $tblYear);
                        }

                        if ($tblDivisionCourse) {
                            $importDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse->getName();
                            $divisionNameList[$divisionName] = $tblDivisionCourse;
                        } else {
                            $missingDivisionCourseList[$divisionName] = $divisionName;
                            $divisionNameList[$divisionName] = false;
                        }
                    }

                    // Lehrer
                    if (!isset($teacherAcronymList[$teacherAcronym])) {
                        // Mapping
                        if (($tblPerson = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_TEACHER_ACRONYM_TO_PERSON_ID, $teacherAcronym))) {
                        // Found
                        } elseif (($tblTeacher = Teacher::useService()->getTeacherByAcronym($teacherAcronym))) {
                            $tblPerson = $tblTeacher->getServiceTblPerson();
                        }

                        if ($tblPerson) {
                            if (($tblTeacherTemp = Teacher::useService()->getTeacherByPerson($tblPerson))
                                && ($temp = $tblTeacherTemp->getAcronym())
                            ) {
                                $displayTeacher = $temp;
                            } else {
                                $displayTeacher = $tblPerson->getLastName();
                            }

                            $importTeacherList[$tblPerson->getId()] = $displayTeacher;
                            $teacherAcronymList[$teacherAcronym] = $tblPerson;
                        } else {
                            $missingTeacherList[$teacherAcronym] = $teacherAcronym;
                            $teacherAcronymList[$teacherAcronym] = false;
                        }
                    }

                    // Fächer
                    if (!isset($subjectAcronymList[$subjectAcronym])) {
                        // Mapping
                        if (($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $subjectAcronym))) {
                        // Found
                        } else {
                            $tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym);
                        }

                        if ($tblSubject) {
                            $importSubjectList[$tblSubject->getId()] = $tblSubject->getAcronym();
                            $subjectAcronymList[$subjectAcronym] = $tblSubject;
                        } else {
                            $missingSubjectList[$subjectAcronym] = $subjectAcronym;
                            $subjectAcronymList[$subjectAcronym] = false;
                        }
                    }

                    // Lehrauftrag
                    if (($tblDivisionCourse = $divisionNameList[$divisionName])
                        && ($tblPerson = $teacherAcronymList[$teacherAcronym])
                        && ($tblSubject = $subjectAcronymList[$subjectAcronym])
                    ) {
                        $subjectGroup = $tblImportLectureship->getSubjectGroup();

                        // Spezialfall: Lehraufträge für SekII -> es werden direkt bei den Lehraufträge die SekII-Kurse zugeordnet, falls vorhanden
                        if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)
                            && ($tblStudentList = $tblDivisionCourse->getStudents())
                        ) {
                            foreach ($tblStudentList as $tblStudent) {
                                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblStudent, $tblYear))
                                    && ($level = $tblStudentEducation->getLevel())
                                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                                ) {
                                    $divisionCourseName = Education::useService()->getCourseNameForSystem(
                                        $tblImport->getExternSoftwareName(), $subjectGroup, $level, $tblSchoolType
                                    );

                                    // mapping SekII-Kurs
                                    if (($tblDivisionCourseCourseSystem = Education::useService()->getImportMappingValueBy(
                                        TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $divisionCourseName, $tblYear
                                    ))) {

                                    // found SekII-Kurs
                                    } elseif (($tblDivisionCourseCourseSystem = DivisionCourse::useService()->getDivisionCourseByNameAndYear(
                                        $divisionCourseName, $tblYear
                                    ))) {

                                    }

                                    if ($tblDivisionCourseCourseSystem
                                        && ($tblDivisionCourseCourseSystem->getServiceTblSubject())
                                        && $tblDivisionCourseCourseSystem->getServiceTblSubject()->getId() == $tblSubject->getId()
                                    ) {
                                        $tblDivisionCourse = $tblDivisionCourseCourseSystem;
                                        $subjectGroup = '';
                                    }

                                    break;
                                }
                            }
                        }

                        // Lehrauftrag existiert bereits
                        if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(
                            $tblYear, $tblPerson, $tblDivisionCourse, $tblSubject
                        ))) {
                            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                                $existsTeacherLectureshipList[$tblTeacherLectureship->getId()] = $tblTeacherLectureship;
                            }
                            // Lehrauftrag wird neu angelegt
                        } else {
                            if ($IsPreview) {
                                $previewCreateTeacherLectureshipList[$tblDivisionCourse->getId() . '_' . $tblPerson->getId() . '_' . $tblSubject->getId()] = 1;
                            } else {
                                $saveCreateTeacherLectureshipList[$tblDivisionCourse->getId() . '_' . $tblPerson->getId() . '_' . $tblSubject->getId()]
                                    = TblTeacherLectureship::withParameter($tblPerson, $tblYear, $tblDivisionCourse, $tblSubject, $subjectGroup);
                            }
                        }
                    }
                }
            }

            // prüfen welche Lehraufträge gelöscht werden
            foreach ($divisionNameList as $tblDivisionCourseTemp) {
                if ($tblDivisionCourseTemp instanceof TblDivisionCourse) {
                    if (($tblTeacherLectureshipListByDivisionCourse = DivisionCourse::useService()->getTeacherLectureshipListBy(
                        $tblYear, null, $tblDivisionCourseTemp
                    ))) {
                        foreach ($tblTeacherLectureshipListByDivisionCourse as $tblTemp) {
                            if (!isset($existsTeacherLectureshipList[$tblTemp->getId()])
                                && ($tblPersonTemp = $tblTemp->getServiceTblPerson())
                                && ($tblSubjectTemp = $tblTemp->getServiceTblSubject())
                            ) {
                                if ($IsPreview) {
                                    $previewDeleteTeacherLectureshipList[$tblTemp->getId()] = $tblDivisionCourseTemp->getName()
                                        . ' - ' . $tblSubjectTemp->getAcronym() . ' - ' . $tblPersonTemp->getFullName();
                                } else {
                                    $saveDeleteTeacherLectureshipList[$tblTemp->getId()] = $tblTemp;
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($IsPreview) {
            return array(
                $importDivisionCourseList,
                $missingDivisionCourseList,
                $importTeacherList,
                $missingTeacherList,
                $importSubjectList,
                $missingSubjectList,
                $previewCreateTeacherLectureshipList,
                $existsTeacherLectureshipList,
                $previewDeleteTeacherLectureshipList
            );
        } else {
            return array(
                $saveCreateTeacherLectureshipList,
                $saveDeleteTeacherLectureshipList
            );
        }
    }
}