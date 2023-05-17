<?php

namespace SPHERE\Application\Transfer\Education;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudent;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudentCourse;
use SPHERE\Common\Frontend\Form\Repository\Button\Danger as DangerButton;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title as TitleTable;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

class FrontendStudentCourse extends Extension implements IFrontendInterface
{
    const LEFT = 3;
    const MIDDLE = 1;
    const RIGHT = 8;
    const RIGHT_PART_1 = 4;
    const RIGHT_PART_2 = 4;

    private array $tabList = array(
        0 => 'Schüler',
        1 => 'Fächer',
        2 => 'SekII-Kurse',
        3 => 'Zusammenfassung / Endgültiger Import'
    );

    /**
     * @param TblImport $tblImport
     * @param string $Tab
     * @param array $tabList
     *
     * @return array
     */
    public function getButtonList(TblImport $tblImport, string $Tab, array $tabList): array
    {
        $buttonList = array();
        foreach ($tabList as $item)
        {
            if ($Tab == $item) {
                $icon = new Edit();
                $name = new Info(new Bold($item));
            } else {
                $icon = null;
                $name = $item;
            }

            $buttonList[] = new Standard($name, $tblImport->getShowRoute(), $icon, array(
                'ImportId' => $tblImport->getId(),
                'Tab' => $item
            ));
        }

        return $buttonList;
    }

    /**
     * @param TblImport $tblImport
     * @param string $Tab
     * @param $Data
     * 
     * @return string
     */
    public function getStudentCourseContent(TblImport $tblImport, string $Tab, $Data = null): string
    {
        // nächsten Tab ermitteln
        $index = array_search($Tab, $this->tabList);
        $NextTab = $this->tabList[$index + 1] ?? '';

        switch ($Tab) {
            case 'Schüler': $content = $this->getStudentContent($tblImport, $NextTab, $Data); break;
            case 'Fächer': $content = $this->getSubjectContent($tblImport, $NextTab, $Data); break;
            case 'SekII-Kurse': $content = $this->getCourseContent($tblImport, $NextTab, $Data); break;
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
    public function getStudentContent(TblImport $tblImport, string $NextTab, $Data): string
    {
        $rows = array();
        if (($tblImportStudentList = $tblImport->getImportStudents())
            && ($tblYear = $tblImport->getServiceTblYear())
        ) {
            $tblPersonList = Education::useService()->getPersonListByIsInCourseSystem($tblYear);

            $global = $this->getGlobal();
            $tblImportStudentList = $this->getSorter($tblImportStudentList)->sortObjectBy('LastFirstName', new StringNaturalOrderSorter());
            /** @var TblImportStudent $tblImportStudent */
            foreach ($tblImportStudentList as $tblImportStudent) {
                $lastFirstName = $tblImportStudent->getLastFirstName();
                if (!$lastFirstName) {
                    continue;
                }

                //  gespeicherte Person
                if (($tblPerson = $tblImportStudent->getServiceTblPerson())) {
                    $status = new Warning(new Bold('Mapping'));
                // Found Person
                } elseif (($tblPerson = Education::useService()->getPersonIsInCourseSystemByFristNameAndLastName(
                    $tblImportStudent->getFirstName(),
                    $tblImportStudent->getLastName(),
                    $tblYear,
                    $tblImportStudent->getBirthday() ?: null
                ))) {
                    $status = new Success(new SuccessIcon());
                // Missing
                } else {
                    $status = new Danger(new Ban());
                }

                // POST setzen
                if ($tblPerson) {
                    $global->POST['Data'][$tblImportStudent->getId()] = $tblPerson->getId();
                    $global->savePost();
                }

                $select = new SelectBox('Data[' . $tblImportStudent->getId() . ']', '', array('{{ LastFirstName }}' => $tblPersonList));
                $rows[] = new LayoutRow(array(
                    new LayoutColumn($lastFirstName, self::LEFT),
                    new LayoutColumn($status, self::MIDDLE),
                    new LayoutColumn($select, self::RIGHT)
                ));
            }
        }

        if (empty($rows)) {
            return new WarningMessage('Es wurden keine Schüler im Import gefunden. Somit können auch keine Schüler-Kurse importiert werden', new Exclamation());
        } else {
            // Kopf erstellen
            $header = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Bold('Schüler-Name in ' . $tblImport->getExternSoftwareName()), self::LEFT),
                new LayoutColumn(new Bold('Status'), self::MIDDLE),
                new LayoutColumn(new Bold('Auswahl Schüler in der Schulsoftware'), self::RIGHT)
            ))));

            $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup($rows))
            )))))->appendFormButton(new Primary('Speichern und Weiter', new Save()));

            return new Title($header)
                . new Well(Education::useService()->saveMappingPerson($form, $tblImport, $NextTab, $Data));
        }
    }

    /**
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return string
     */
    public function getSubjectContent(TblImport $tblImport, string $NextTab, $Data): string
    {
        $rows = array();
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        $subjectAcronymList = array();

        if ($tblImport->getTypeIdentifier() == TblImport::TYPE_IDENTIFIER_LECTURESHIP) {
            $tblImportItemList = $tblImport->getImportLectureships();
            $text = 'Lehraufträge';
        } else {
            $tblImportItemList = $tblImport->getImportStudentCourses();
            $text = 'Schüler-Kurse';
        }

        if ($tblImportItemList) {
            $global = $this->getGlobal();
            $tblImportItemList = $this->getSorter($tblImportItemList)->sortObjectBy('SubjectAcronym', new StringNaturalOrderSorter());
            /** @var TblImportLectureship|TblImportStudentCourse $tblImportItem */
            foreach ($tblImportItemList as $tblImportItem) {
                if (($subjectAcronym = $tblImportItem->getSubjectAcronym())
                    && !isset($subjectAcronymList[$subjectAcronym])
                ) {
                    $subjectAcronymList[$subjectAcronym] = $subjectAcronym;

                    // Mapping
                    if (($tblSubject = Education::useService()->getImportMappingValueBy(TblImportMapping::TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID, $subjectAcronym))) {
                        $status = new Warning(new Bold('Mapping'));
                        // Found
                    } elseif (($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))) {
                        $status = new Success(new SuccessIcon());
                        // Missing
                    } else {
                        $status = new Danger(new Ban());
                    }

                    // POST setzen
                    if ($tblSubject) {
                        $global->POST['Data'][$tblImportItem->getId()] = $tblSubject->getId();
                        $global->savePost();
                    }

                    $select = new SelectBox('Data[' . $tblImportItem->getId() . ']', '', array('{{ DisplayName }}' => $tblSubjectAll));
                    $rows[] = new LayoutRow(array(
                        new LayoutColumn($subjectAcronym, self::LEFT),
                        new LayoutColumn($status, self::MIDDLE),
                        new LayoutColumn($select, self::RIGHT)
                    ));
                }
            }
        }

        if (empty($rows)) {
            return new WarningMessage('Es wurden keine Fächer-Kürzel im Import gefunden. Somit können auch keine ' . $text . ' importiert werden', new Exclamation());
        } else {
            // Kopf erstellen
            $header = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Bold('Fächer-Kürzel in ' . $tblImport->getExternSoftwareName()), self::LEFT),
                new LayoutColumn(new Bold('Status'), self::MIDDLE),
                new LayoutColumn(new Bold('Auswahl Fach in der Schulsoftware'), self::RIGHT)
            ))));

            $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup($rows))
            )))))->appendFormButton(new Primary('Speichern und Weiter', new Save()));

            return new Title($header)
                . new Well(Education::useService()->saveMappingSubject($form, $tblImport, $NextTab, $Data));
        }
    }

    /**
     * @param TblImport $tblImport
     * @param string $NextTab
     * @param $Data
     *
     * @return string
     */
    public function getCourseContent(TblImport $tblImport, string $NextTab, $Data): string
    {
        $rows = array();
        $courseNameList = array();
        $importStudentEducationList = array();
        if (($tblYear = $tblImport->getServiceTblYear())
            && ($tblImportStudentCourseList = $tblImport->getImportStudentCourses())
        ) {
            $tblDivisionCourseList = array();
            if (($tblDivisionCourseListAdvancedCourse = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_ADVANCED_COURSE))) {
                $tblDivisionCourseList = $tblDivisionCourseListAdvancedCourse;
            }
            if (($tblDivisionCourseListBasicCourse = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_BASIC_COURSE))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListBasicCourse);
            }

            $global = $this->getGlobal();
            $tblImportStudentCourseList = $this->getSorter($tblImportStudentCourseList)->sortObjectBy('CourseName', new StringNaturalOrderSorter());
            /** @var TblImportStudentCourse $tblImportStudentCourse */
            foreach ($tblImportStudentCourseList as $tblImportStudentCourse) {
                if (($courseName = $tblImportStudentCourse->getCourseName())
                    && ($tblImportStudent = $tblImportStudentCourse->getTblImportStudent())
                ) {
                    if (isset($importStudentEducationList[$tblImportStudent->getId()])) {
                        $tblStudentEducation = $importStudentEducationList[$tblImportStudent->getId()];
                    } else {
                        $tblStudentEducation = $tblImportStudent->getStudentEducation();
                        $importStudentEducationList[$tblImportStudent->getId()] = $tblStudentEducation;
                    }

                    if ($tblStudentEducation
                        && ($level = $tblStudentEducation->getLevel())
                        && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    ) {
                        $courseNameExternSoftware = $level . $tblSchoolType->getShortName() . ' ' . $courseName;
                        $courseName = Education::useService()->getCourseNameForSystem($tblImport->getExternSoftwareName(), $courseName, $level, $tblSchoolType);

                        if (!isset($courseNameList[$courseName])) {
                            $courseNameList[$courseName] = $courseName;

                            // Mapping
                            if (($tblDivisionCourse = Education::useService()->getImportMappingValueBy(
                                TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $courseName, $tblYear
                            ))) {
                                $status = new Warning(new Bold('Mapping'));
                            // Found
                            } elseif (($tblDivisionCourse = Education::useService()->getDivisionCourseCourseSystemByCourseNameAndYear($courseName, $tblYear))) {
                                $status = new Success(new SuccessIcon());
                            // Missing
                            } else {
                                $status = new Danger(new Ban());
                            }

                            // POST setzen
                            if ($tblDivisionCourse) {
                                $global->POST['Data']['Select'][$tblImportStudentCourse->getId()] = $tblDivisionCourse->getId();
                                $global->savePost();

                                $checkInput = '';
                            } else {
                                $global->POST['Data']['Check'][$tblImportStudentCourse->getId()] = 1;
                                $global->savePost();
                                $checkInput = new CheckBox('Data[Check][' . $tblImportStudentCourse->getId() . ']', $courseName, 1);
                            }

                            $select = new SelectBox('Data[Select][' . $tblImportStudentCourse->getId() . ']', '',
                                array('{{ DisplayName }}' => $tblDivisionCourseList));
//                                array('{{ DisplayName }} / {{ TypeName }} / {{ SubjectName }}' => $tblDivisionCourseList));
                            $rows[] = new LayoutRow(array(
                                new LayoutColumn($courseNameExternSoftware, self::LEFT),
                                new LayoutColumn($status, self::MIDDLE),
                                new LayoutColumn($select, self::RIGHT_PART_1),
                                new LayoutColumn($checkInput, self::RIGHT_PART_1)
                            ));
                        }
                    }
                }
            }
        }

        if (empty($rows)) {
            return new WarningMessage('Es wurden keine SekII-Kursnamen im Import gefunden. Somit können auch keine Schüler-Kurse importiert werden', new Exclamation());
        } else {
            // Kopf erstellen
            $header = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(new Bold('Kursname in ' . $tblImport->getExternSoftwareName()), self::LEFT),
                new LayoutColumn(new Bold('Status'), self::MIDDLE),
                new LayoutColumn(new Bold('Auswahl SekII-Kurs in der Schulsoftware'), self::RIGHT_PART_1),
                new LayoutColumn(new Bold('oder SekII-Kurs neu anlegen in der Schulsoftware'), self::RIGHT_PART_2)
            ))));

            $form = (new Form(new FormGroup(new FormRow(new FormColumn(
                new Layout(new LayoutGroup($rows))
            )))))->appendFormButton(new Primary('Speichern und Weiter', new Save()));

            return new Title($header)
                . new Well(Education::useService()->saveMappingStudentCourse($form, $tblImport, $NextTab, $Data, $tblYear));
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
            $dataList,
            $headerList,
            $missingPersonList,
            $missingCourseList,
            $previewDeleteStudentSubjectList
            ) = $this->getImportStudentCoursePreviewData($tblImport, true);

        // sortieren
        sort($missingPersonList, SORT_NATURAL);
        sort($missingCourseList, SORT_NATURAL);
        ksort($headerList[1], SORT_NATURAL);
        ksort($headerList[2], SORT_NATURAL);
        $headerList[1] = array('LastFirstName' => 'Schüler') + $headerList[1];
        $headerList[2] = array('LastFirstName' => 'Schüler') + $headerList[2];

        return
            ($missingPersonList ? new Panel('Schüler werden nicht importiert', $missingPersonList, Panel::PANEL_TYPE_DANGER) : '')
            . ($missingCourseList ? new Panel('SekII-Kurse werden nicht importiert', $missingCourseList, Panel::PANEL_TYPE_DANGER) : '')
            . (new TableData($dataList[1], new TitleTable('1. Halbjahr'), $headerList[1], array('responsive' => false, 'paging' => false)))
                ->setHash('Table-1.Halbjahr')
            . (new TableData($dataList[2], new TitleTable('2. Halbjahr'), $headerList[2], array('responsive' => false, 'paging' => false)))
                ->setHash('Table-2.Halbjahr')
            . new Container('&nbsp;')
            . ($previewDeleteStudentSubjectList
                ? new Panel(
                    new Minus() . '  Es werden ' . count($previewDeleteStudentSubjectList) . ' Schüler-Fächer gelöscht!',
                    $previewDeleteStudentSubjectList,
                    Panel::PANEL_TYPE_DANGER
                )
                : '')
            . Education::useService()->saveStudentCoursesFromImport(
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
    public function getImportStudentCoursePreviewData(TblImport $tblImport, bool $IsPreview): array
    {
        $dataList[1] = array();
        $dataList[2] = array();
        $headerList[1] = array();
        $headerList[2] = array();
        $missingPersonList = array();
        $missingCourseList = array();
        $studentCourseList = array();
        $previewDeleteStudentSubjectList = array();

        $saveCreateStudentSubjectList = array();
        $saveDeleteStudentSubjectList = array();

        if (($tblImportStudentList = $tblImport->getImportStudents())
            && ($tblYear = $tblImport->getServiceTblYear())
        ) {
            $tblImportStudentList = $this->getSorter($tblImportStudentList)->sortObjectBy('LastFirstName', new StringNaturalOrderSorter());
            /** @var TblImportStudent $tblImportStudent */
            foreach ($tblImportStudentList as $tblImportStudent) {
                $lastFirstName = $tblImportStudent->getLastFirstName();
                if (!$lastFirstName) {
                    continue;
                }

                // gespeicherte Person
                if (($tblPerson = $tblImportStudent->getServiceTblPerson())) {
                // Found Person
                } elseif (($tblPerson = Education::useService()->getPersonIsInCourseSystemByFristNameAndLastName(
                    $tblImportStudent->getFirstName(),
                    $tblImportStudent->getLastName(),
                    $tblYear,
                    $tblImportStudent->getBirthday() ?: null
                ))) {

                }

                if ($tblPerson) {
                    $item = array();
                    $item[1]['LastFirstName'] = $lastFirstName;
                    $item[2]['LastFirstName'] = $lastFirstName;
                    if (($tblStudentEducation = $tblImportStudent->getStudentEducation())
                        && ($level = $tblStudentEducation->getLevel())
                        && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                        && ($tblImportStudentCourseList = Education::useService()->getImportStudentCourseListByImportStudent($tblImportStudent))
                    ) {
                        foreach ($tblImportStudentCourseList as $tblImportStudentCourse) {
                            if (($subjectAcronym = $tblImportStudentCourse->getSubjectAcronym())
                                && ($courseName = $tblImportStudentCourse->getCourseName())
                            ) {
                                // Indiware beinhaltet alle 4 Halbjahre
                                if ($tblImport->getExternSoftwareName() == TblImport::EXTERN_SOFTWARE_NAME_INDIWARE) {
                                    $coursePeriod = intval(substr($tblImportStudentCourse->getCourseNumber(), 0, 1));
                                    // nur das ausgewählte Schuljahr importieren, es sind allerdings alle 4. Halbjahre im Import bei Indiware
                                    if ($coursePeriod < 3) {
                                        if (($tblSchoolType->getShortName() == 'Gy' && $level == 12)
                                            || ($tblSchoolType->getShortName() == 'BGy' && $level == 13)
                                        ) {
                                            continue;
                                        }
                                    } else {
                                        if (($tblSchoolType->getShortName() == 'Gy' && $level == 11)
                                            || ($tblSchoolType->getShortName() == 'BGy' && $level == 12)
                                        ) {
                                            continue;
                                        }
                                    }
                                    if ($coursePeriod == 1 || $coursePeriod == 3) {
                                        $period = 1;
                                    } else {
                                        $period = 2;
                                    }

                                // Untis
                                } else {
                                    $period = $tblImportStudentCourse->getCourseNumber();
                                }

                                $courseName = Education::useService()->getCourseNameForSystem($tblImport->getExternSoftwareName(), $courseName, $level, $tblSchoolType);

                                if (!isset($headerList[$period][$subjectAcronym])) {
                                    $headerList[$period][$subjectAcronym] = $subjectAcronym;
                                }

                                // Mapping SekII-Kurs
                                if (($tblDivisionCourse = Education::useService()->getImportMappingValueBy(
                                    TblImportMapping::TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME, $courseName, $tblYear
                                ))) {
                                    // Found SekII-Kurs
                                } elseif (($tblDivisionCourse = Education::useService()->getDivisionCourseCourseSystemByCourseNameAndYear($courseName, $tblYear))) {

                                }

                                if ($tblDivisionCourse) {
                                    $periodIdentifier = $level . '/' . $period;
                                    $studentCourseList[$tblPerson->getId()][$periodIdentifier][$tblDivisionCourse->getId()] = 1;

                                    $existsStudentSubject = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndDivisionCourseAndPeriod(
                                        $tblPerson, $tblYear, $tblDivisionCourse, $period
                                    );

                                    if ($IsPreview) {
                                        $item[$period][$subjectAcronym] = $existsStudentSubject
                                            ? $tblDivisionCourse->getName()
                                            : new Success(new Plus() . $tblDivisionCourse->getName());
                                    } elseif (!$existsStudentSubject) {
                                        // doppelte Einträge bei Untis (A- und B-Woche) ignorieren
                                        $key = $tblPerson->getId() . '_' . $periodIdentifier . '_' . $tblDivisionCourse->getId();
                                        $saveCreateStudentSubjectList[$key] = TblStudentSubject::withParameter(
                                            $tblPerson,
                                            $tblYear,
                                            null,
                                            true,
                                            null,
                                            $tblDivisionCourse,
                                            $periodIdentifier
                                        );
                                    }
                                } else {
                                    $missingCourseList[$courseName] = $courseName;
                                    $item[$period][$subjectAcronym] = new Danger('Kurs: ' . $courseName . ' ist nicht vorhanden');
                                }
                            }
                        }
                    }

                    if ($IsPreview) {
                        $dataList[1][$tblPerson->getId()] = $item[1];
                        $dataList[2][$tblPerson->getId()] = $item[2];
                    }

                    // prüfen welche Schüler-Fächer gelöscht werden
                    if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
                        foreach ($tblStudentSubjectList as $tblStudentSubject) {
                            if (($tblSubjectDivisionCourse = $tblStudentSubject->getTblDivisionCourse())
                                && !isset($studentCourseList[$tblPerson->getId()][$tblStudentSubject->getPeriodIdentifier()][$tblSubjectDivisionCourse->getId()])
                            ) {
                                if ($IsPreview) {
                                    $previewDeleteStudentSubjectList[] = $tblPerson->getLastFirstName()
                                        . ' - ' . $tblStudentSubject->getPeriodIdentifier()
                                        . ' - ' . $tblSubjectDivisionCourse->getName();
                                } else {
                                    $saveDeleteStudentSubjectList[] = $tblStudentSubject;
                                }
                            }
                        }
                    }
                } else {
                    $missingPersonList[] = $lastFirstName;
                }
            }
        }

        if ($IsPreview) {
            return array(
                $dataList,
                $headerList,
                $missingPersonList,
                $missingCourseList,
                $previewDeleteStudentSubjectList
            );
        } else {
            return array(
                $saveCreateStudentSubjectList,
                $saveDeleteStudentSubjectList
            );
        }
    }
}