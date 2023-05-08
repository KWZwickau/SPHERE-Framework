<?php

namespace SPHERE\Application\Transfer\Education;

use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportLectureship;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportMapping;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudent;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImportStudentCourse;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
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
//            case 'Zusammenfassung / Endgültiger Import': $content = $this->getImportPreviewContent($tblImport); break;
//            case 'SaveTeacherLectureship': $content = $this->getSaveTeacherLectureshipContent($tblImport); break;
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
}