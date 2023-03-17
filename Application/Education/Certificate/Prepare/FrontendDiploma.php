<?php

namespace SPHERE\Application\Education\Certificate\Prepare;

use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\ResizeVertical;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

abstract class FrontendDiploma extends Extension implements IFrontendInterface
{
    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param null $Route
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDroppedSubjects($PrepareId = null, $PersonId = null, $Route = null, $Data = null)
    {
        $Stage = new Stage('Abgewählte Fächer', 'Verwalten');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare/Preview', new ChevronLeft(), array(
                'PrepareId' => $PrepareId,
                'Route' => $Route,
            )
        ));

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $contentList = array();
            if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
                && ($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson, $tblPrepareAdditionalGradeType))
            ) {
                $count = 1;
                foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                    if (($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject())) {
                        $contentList[] = array(
                            'Ranking' => $count++,
                            'Acronym' => new PullClear(
                                new PullLeft(new ResizeVertical() . ' ' . $tblSubject->getAcronym())
                            ),
                            'Name' => $tblSubject->getName(),
                            'Grade' => $tblPrepareAdditionalGrade->getGrade(),
                            'Option' => (new Standard('', '/Education/Certificate/Prepare/DroppedSubjects/Destroy',
                                new Remove(),
                                array(
                                    'Id' => $tblPrepareAdditionalGrade->getId(),
                                    'Route' => $Route,
                                ),
                                'Löschen'
                            ))
                        );
                    }
                }
            }

            $form = $this->formCreatePrepareAdditionalGrade($tblPrepare, $tblPerson);
            $form->appendFormButton(new Primary('Speichern', new Save()));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(
                                    'Zeugnisvorbereitung',
                                    array(
                                        $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                        $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(
                                    'Schüler',
                                    array(
                                        $tblPerson->getLastFirstName()
                                    ),
                                    Panel::PANEL_TYPE_INFO
                                ),
                            ), 6),
                            new LayoutColumn(array(
                                new TableData(
                                    $contentList,
                                    null,
                                    array(
                                        'Ranking' => '#',
                                        'Acronym' => 'Kürzel',
                                        'Name' => 'Name',
                                        'Grade' => 'Zensur',
                                        'Option' => ''
                                    ),
                                    array(
                                        'rowReorderColumn' => 1,
                                        'ExtensionRowReorder' => array(
                                            'Enabled' => true,
                                            'Url' => '/Api/Education/Prepare/Reorder',
                                            'Data' => array(
                                                'PrepareId' => $tblPrepare->getId(),
                                                'PersonId' => $tblPerson->getId()
                                            )
                                        ),
                                        'paging' => false,
                                    )
                                )
                            ))
                        ))
                    ), new Title(new ListingTable() . ' Übersicht')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Well(Prepare::useService()->createPrepareAdditionalGradeForm(
                                    $form,
                                    $Data,
                                    $tblPrepare,
                                    $tblPerson,
                                    $Route
                                ))
                            )
                        ))
                    ), new Title(new PlusSign() . ' Hinzufügen'))
                ))
            );

            return $Stage;

        } else {

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    /**
     * @param TblPrepareCertificate $tblPrepareCertificate
     * @param TblPerson $tblPerson
     *
     * @return Form
     */
    private function formCreatePrepareAdditionalGrade(
        TblPrepareCertificate $tblPrepareCertificate,
        TblPerson $tblPerson
    ): Form {
        $availableSubjectList = array();
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('PRIOR_YEAR_GRADE'))
            && $tblSubjectAll
            && ($tempList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepareCertificate, $tblPerson, $tblPrepareAdditionalGradeType))
        ) {
            $usedSubjectList = array();
            foreach ($tempList as $item) {
                if ($item->getServiceTblSubject()) {
                    $usedSubjectList[$item->getServiceTblSubject()->getId()] = $item;
                }
            }

            foreach ($tblSubjectAll as $tblSubject) {
                if (!isset($usedSubjectList[$tblSubject->getId()])) {
                    $availableSubjectList[] = $tblSubject;
                }
            }
        } else {
            $availableSubjectList = $tblSubjectAll;
        }

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new SelectBox('Data[Subject]', 'Fach', array('DisplayName' => $availableSubjectList)), 6
                    ),
                    new FormColumn(
                        new TextField('Data[Grade]', '', 'Zensur'), 6
                    )
                ))
            ))
        ));
    }

    /**
     * @param null $Id
     * @param null $Confirm
     * @param null $Route
     *
     * @return Stage|string
     */
    public function frontendDestroyDroppedSubjects(
        $Id = null,
        $Confirm = null,
        $Route = null
    ) {
        $Stage = new Stage('Abgewähltes Fach', 'Löschen');
        if (($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeById($Id))
            && ($tblPrepare = $tblPrepareAdditionalGrade->getTblPrepareCertificate())
            && ($tblPerson = $tblPrepareAdditionalGrade->getServiceTblPerson())
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $parameters = array(
                'PrepareId' => $tblPrepare->getId(),
                'PersonId' => $tblPerson->getId(),
                'Route' => $Route
            );

            $Stage->addButton(
                new Standard('Zur&uuml;ck', '/Education/Certificate/Prepare/DroppedSubjects', new ChevronLeft(), $parameters)
            );

            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnisvorbereitung',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate())),
                                    $tblDivisionCourse->getTypeName() . ': ' . $tblDivisionCourse->getDisplayName()
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                'Schüler',
                                array(
                                    $tblPerson->getLastFirstNameWithCallNameUnderline()
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                                new Panel(
                                    'Abgewähltes Fach',
                                    ($tblSubject = $tblPrepareAdditionalGrade->getServiceTblSubject()) ? $tblSubject->getName() : '',
                                    Panel::PANEL_TYPE_INFO
                                ),
                                new Panel(new Question() . ' Dieses abgewählte Fach wirklich löschen?',
                                    array(
                                        $tblSubject ? 'Fach-Kürzel: ' . $tblSubject->getAcronym() : null,
                                        $tblSubject ? 'Fach-Name: ' . $tblSubject->getName() : null,
                                        'Zensur: ' . $tblPrepareAdditionalGrade->getGrade()
                                    ),
                                    Panel::PANEL_TYPE_DANGER,
                                    new Standard(
                                        'Ja', '/Education/Certificate/Prepare/DroppedSubjects/Destroy', new Ok(),
                                        array('Id' => $Id, 'Confirm' => true, 'Route' => $Route)
                                    )
                                    . new Standard('Nein', '/Education/Certificate/Prepare/DroppedSubjects', new Disable(), $parameters)
                                )
                            )
                        )
                    ))))
                );
            } else {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Prepare::useService()->destroyPrepareAdditionalGrade($tblPrepareAdditionalGrade)
                                ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Das abgewählte Fach wurde gelöscht')
                                : new Danger(new Ban() . ' Das abgewählte Fach konnte nicht gelöscht werden')
                            ),
                            new Redirect('/Education/Certificate/Prepare/DroppedSubjects', Redirect::TIMEOUT_SUCCESS, $parameters)
                        )))
                    )))
                );
            }
        } else {
            return $Stage . new Danger('Abgewähltes Fach nicht gefunden.', new Ban());
        }

        return $Stage;
    }

    /**
     * @param $PrepareId
     * @param string $Route
     * @param string $SchoolTypeShortName
     * @param string $Tab
     * @param $Data
     *
     * @return Stage|string
     */
    public function frontendPrepareDiplomaSetting(
        $PrepareId = null,
        string $Route = '',
        string $SchoolTypeShortName = '',
        string $Tab = '',
        $Data = null
    ) {
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $tabList = $this->getTabList($tblDivisionCourse);
            if ($Tab == '') {
                $Tab = reset($tabList);
            }

            if (strpos($Tab, 'Subject-') !== false) {
                $acronym = str_replace('Subject-', '', $Tab);
                if (($tblSubject = Subject::useService()->getSubjectByVariantAcronym($acronym))) {
                    return $this->getSubjectContent($tblPrepare, $tblDivisionCourse, $Route, $tblSubject, $Data, $SchoolTypeShortName, $Tab, $tabList);
                } else {
                    return $this->getStageInformation($tblPrepare, $tblDivisionCourse, $Route, $Data, $SchoolTypeShortName, $Tab, $tabList);
                }
//            } elseif (strpos($Tab, 'SubjectVariable') !== false) {
//                return $this->getSubjectContent($tblPrepare, $tblDivisionCourse, $Route, null, $Data, $SchoolTypeShortName, $Tab, $tabList);
            } else {
                return $this->getStageInformation($tblPrepare, $tblDivisionCourse, $Route, $Data, $SchoolTypeShortName, $Tab, $tabList);
            }
        } else {
            $Stage = new Stage('Zeugnisvorbereitung', 'Einstellungen');
            $Stage->addButton(new Standard(
                'Zurück', '/Education/Certificate/Prepare', new ChevronLeft()
            ));

            return $Stage . new Danger('Zeugnisvorbereitung nicht gefunden.', new Ban());
        }
    }

    private function getTabList(TblDivisionCourse $tblDivisionCourse): array
    {
        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse))) {
            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
            /** @var TblSubject $tblSubject */
            foreach ($tblSubjectList as $tblSubject) {
                $tabList[] = 'Subject-' . $tblSubject->getAcronym();
            }
        }
        $tabList[] = 'Information';

        return $tabList;
    }

//    private function getTabList(string $SchoolTypeShortName): array
//    {
//        switch ($SchoolTypeShortName) {
//            case 'OS':
//            case 'FOS':
//            default:
//                return array(
//                    0 => 'Subject-DE',
//                    1 => 'Subject-MA',
//                    2 => 'Subject-EN',
//                    3 => 'SubjectVariable-4',
//                    4 => 'SubjectVariable-5',
//                    5 => 'Information'
//                );
//        }
//    }

    private function getButtonList($PrepareId, string $Route, string $SchoolTypeShortName, string $CurrentTab, array $tabList): array
    {
        $buttonList = array();
        foreach ($tabList as $tab) {
            if (strpos($tab, 'Subject-') !== false) {
                $name = str_replace('Subject-', '', $tab);
//            } elseif (strpos($tab, 'SubjectVariable') !== false) {
//                $name = str_replace('SubjectVariable-', '', $tab) . '. Prüfungsfach';
            } else {
                $name = 'Sonstige Informationen';
            }

            if ($tab == $CurrentTab) {
                $icon = new Edit();
                $name = new Info(new Bold($name));
            } else {
                $icon = null;
            }

            $buttonList[] = new Standard($name, '/Education/Certificate/Prepare/Prepare/Diploma/Setting', $icon, array(
                'PrepareId' => $PrepareId,
                'Route' => $Route,
                'SchoolTypeShortName' => $SchoolTypeShortName,
                'Tab' => $tab
            ));
        }

        return $buttonList;
    }

    private function getStageInformation(TblPrepareCertificate $tblPrepare, TblDivisionCourse $tblDivisionCourse, string $Route, $Data,
        string $SchoolTypeShortName, string $CurrentTab, array $tabList): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Sonstige Informationen festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
            array(
                'DivisionId' => $tblDivisionCourse->getId(),
                'Route' => $Route
            )
        ));

        $buttonList = $this->getButtonList($tblPrepare->getId(), $Route, $SchoolTypeShortName, $CurrentTab, $tabList);
        $CertificateList = array();
        Prepare::useFrontend()->getInformationContent($tblPrepare, $Route, $CertificateList, $Stage, $Data, $buttonList, null, null, null);

        return $Stage;
    }

    private function getSubjectContent(TblPrepareCertificate $tblPrepare, TblDivisionCourse $tblDivisionCourse, string $Route, TblSubject $tblSubject, $Data,
        string $SchoolTypeShortName, string $CurrentTab, array $tabList): Stage
    {
        $Stage = new Stage('Zeugnisvorbereitung', 'Fachnoten festlegen');
        $Stage->addButton(new Standard(
            'Zurück', '/Education/Certificate/Prepare/Prepare', new ChevronLeft(),
            array(
                'DivisionId' => $tblDivisionCourse->getId(),
                'Route' => $Route
            )
        ));

        $columnTable = array(
            'Number' => '#',
            'Name' => 'Name',
            'IntegrationButton' => 'Integration',
            'Course' => 'Bildungsgang'
        );

        // GradeTexts
        $selectListGradeTexts = array();
        if (($tblGradeTextList = Grade::useService()->getGradeTextAll())) {
            $selectListGradeTexts = $tblGradeTextList;
        }

//        // Variable Prüfungsfächer
//        $ranking = 0;
//        if (!$tblSubject) {
//            $ranking = intval(str_replace('SubjectVariable-', '', $CurrentTab));
//            $columnTable['Subject'] = 'Fach';
//        }

        $columnTable['JN'] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier('JN'))
            ? $tblPrepareAdditionalGradeType->getName() : 'JN';
        $isLevel9OS = Prepare::useService()->getHasPrepareLevel9OS($tblPrepare);
        $keyList = $this->getKeyList($isLevel9OS);
        foreach ($keyList as $item) {
            $columnTable[$item] = ($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($item))
                ? $tblPrepareAdditionalGradeType->getName() : $item;
        }

        $columnTable['Average'] = '&#216;';
        $columnTable['EN'] = 'En (Endnote)';
        $columnTable['Text'] = 'oder Zeugnistext';

        $buttonList = $this->getButtonList($tblPrepare->getId(), $Route, $SchoolTypeShortName, $CurrentTab, $tabList);
        $studentTable = array();
        $tblTask = $tblPrepare->getServiceTblAppointedDateTask();
        if (($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $count = 0;
            foreach ($tblPersonList as $tblPerson) {
                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson);
                $studentTable[$tblPerson->getId()] = Prepare::useFrontend()->getStudentBasicInformation($tblPerson, $tblYear, $tblPrepareStudent ?: null, $count);
                if ($tblPrepareStudent
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
                    $gradeList = array();
                    if (($tblPrepareAdditionalGradeList = Prepare::useService()->getPrepareAdditionalGradeListBy($tblPrepare, $tblPerson))) {
                        $Global = $this->getGlobal();
                        foreach ($tblPrepareAdditionalGradeList as $tblPrepareAdditionalGrade) {
                            if ($tblPrepareAdditionalGrade->getServiceTblSubject()
                                && ($tblPrepareAdditionalGradeType = $tblPrepareAdditionalGrade->getTblPrepareAdditionalGradeType())
                                && $tblPrepareAdditionalGradeType->getIdentifier() != 'PRIOR_YEAR_GRADE'
                                &&  $tblSubject->getId() == $tblPrepareAdditionalGrade->getServiceTblSubject()->getId()
                            ) {
                                // Zeugnistext
                                if ($tblPrepareAdditionalGradeType->getIdentifier() == 'EN'
                                    && ($tblGradeText = Grade::useService()->getGradeTextByName($tblPrepareAdditionalGrade->getGrade()))
                                ) {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()]['Text'] = $tblGradeText->getId();
                                } else {
                                    $Global->POST['Data'][$tblPrepareStudent->getId()][$tblPrepareAdditionalGradeType->getIdentifier()] = $tblPrepareAdditionalGrade->getGrade();
                                    if ($tblPrepareAdditionalGrade->getGrade()) {
                                        $gradeList[$tblPrepareAdditionalGradeType->getIdentifier()] = $tblPrepareAdditionalGrade->getGrade();
                                    }
                                }
                            }
                        }
                        $Global->savePost();
                    }

                    $isApproved = $tblPrepareStudent->isApproved();
                    $preName = 'Data[' . $tblPrepareStudent->getId() . ']';

//                    if (!$tblSubject) {
//                        $selectBoxSubject = new SelectBox($preName . '[Subject]', '', array('{{ Name }}' => $subjectList));
//                        if ($isApproved) {
//                            $selectBoxSubject->setDisabled();
//                        }
//                        $studentTable[$tblPerson->getId()]['Subject'] = $selectBoxSubject;
//                    }

                    $jn = '';
                    if ($tblTask
                        && ($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblTask, $tblSubject))
                    ) {
                        $jn = $tblTaskGrade->getDisplayGrade(true, $tblCertificate);
                        if (is_numeric($jn)) {
                            $gradeList['JN'] = $jn;
                        }
                    }
                    $studentTable[$tblPerson->getId()]['JN'] = $jn;

                    $pipeLineList = array();
                    if (!$isApproved) {
                        $pipeLineList[] = ApiPrepare::pipelineLoadDiplomaAverage($tblPrepareStudent->getId(), 'Average', $jn, $SchoolTypeShortName);
                        if (!isset($gradeList['EN'])) {
                            $pipeLineList[] = ApiPrepare::pipelineLoadDiplomaAverage($tblPrepareStudent->getId(), 'EN', $jn, $SchoolTypeShortName);
                        }
                    }

                    foreach ($keyList as $key) {
                        $studentTable[$tblPerson->getId()][$key] = $this->getTextField($preName, $key, $isApproved, $pipeLineList);
                    }

                    if (!$isApproved && !isset($gradeList['EN'])) {
                        $gradeInput = ApiPrepare::receiverContent(
                            $this->getTextFieldCertificateGrade($preName, $tblPrepareStudent->getId()), 'Diploma_EN_' . $tblPrepareStudent->getId()
                        );
                    } else {
                        $gradeInput = $this->getTextField($preName, 'EN', $isApproved, array());
                    }

                    $gradeTextSelectBox = new SelectBox($preName . '[Text]', '', array(TblGradeText::ATTR_NAME => $selectListGradeTexts));
                    if ($isApproved) {
                        $gradeTextSelectBox->setDisabled();
                    }

                    $studentTable[$tblPerson->getId()]['Average'] = ApiPrepare::receiverContent(
                        Prepare::useService()->getCalcDiplomaGrade($gradeList, 'Average', $SchoolTypeShortName != 'OS'),
                        'Diploma_Average_' . $tblPrepareStudent->getId()
                    );
                    $studentTable[$tblPerson->getId()]['EN'] = $gradeInput;
                    $studentTable[$tblPerson->getId()]['Text'] = $gradeTextSelectBox;
                }

                // leere Elemente auffühlen (sonst steht die Spaltennummer drin)
                foreach ($columnTable as $columnKey => $columnName) {
                    foreach ($studentTable as $personId => $value) {
                        if (!isset($studentTable[$personId][$columnKey])) {
                            $studentTable[$personId][$columnKey] = '';
                        }
                    }
                }
            }
        }

        $Interactive = array(
            "columnDefs" => array(
                array(
                    "width" => "18px",
                    "targets" => 0
                ),
                array(
                    "width" => "200px",
                    "targets" => 1
                ),
                array(
                    "width" => "80px",
                    "targets" => 2
                ),
            ),
            'order' => array(
                array('0', 'asc'),
            ),
            "paging" => false, // Deaktivieren Blättern
            "iDisplayLength" => -1,    // Alle Einträge zeigen
            "searching" => false, // Deaktivieren Suchen
            "info" => false,  // Deaktivieren Such-Info
            "sort" => false,
            "responsive" => false
        );

        $tableTitle = new \SPHERE\Common\Frontend\Table\Repository\Title('Prüfungsfach: ' . $tblSubject->getDisplayName());
        $tableData = new TableData($studentTable, $tableTitle, $columnTable, $Interactive, true);
        $form = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        $tableData
                    ),
                    new FormColumn(new HiddenField('Data[IsSubmit]'))
                )),
            )),
            new Primary('Speichern', new Save())
        );

        if (($position = array_search($CurrentTab, $tabList)) !== false) {
            $NextTab = $tabList[$position + 1] ?? '';
        } else {
            $NextTab = '';
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(
                                'Zeugnis',
                                array(
                                    $tblPrepare->getName() . ' ' . new Small(new Muted($tblPrepare->getDate()))
                                ),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(
                                $tblDivisionCourse->getTypeName(),
                                $tblDivisionCourse->getDisplayName(),
                                Panel::PANEL_TYPE_INFO
                            ),
                        ), 6),
                        new LayoutColumn($buttonList),
                    )),
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            ApiSupportReadOnly::receiverOverViewModal(),
                            Prepare::useService()->updatePrepareExamGrades(
                                $form,
                                $tblPrepare,
                                $tblSubject,
                                $Route,
                                $NextTab,
                                $SchoolTypeShortName,
                                $Data
                            )
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    private function getKeyList(bool $isLevel9): array
    {
        if ($isLevel9) {
            return array('LS', 'LM');
        } else {
            return array('PS', 'PM', 'PZ');
        }
    }

    private function getTextField(string $preName, string $key, bool $isApproved, array $pipelineList): TextField
    {
        $textField = new TextField($preName . '[' .$key . ']', '', '');
        if ($isApproved) {
            $textField->setDisabled();
        } elseif ($pipelineList) {
            $textField->ajaxPipelineOnKeyUp($pipelineList);
        }

        return $textField;
    }

    /**
     * @param string $preName
     * @param $prepareStudentId
     * @param $postValue
     *
     * @return TextField
     */
    public function getTextFieldCertificateGrade(string $preName, $prepareStudentId, $postValue = null): TextField
    {
        // doch erstmal nicht aus Platz gründen
        $prefix = '';
        if ($postValue) {
            $global = $this->getGlobal();
            $global->POST['Data'][$prepareStudentId]['EN'] = $postValue;
            $global->savePost();
            $prefix = 'Vorschlag';
        }

        $textField = new TextField($preName . '[EN]', '', '');
        if ($prefix) {
            $textField->setPrefixValue($prefix);
        }

        return $textField;
    }
}