<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiSubjectTable;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Link as LinkIcon;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Window\Stage;

class FrontendSubjectTable extends FrontendStudentSubject
{
    /**
     * @param $SchoolTypeId
     *
     * @return Stage
     */
    public function frontendSubjectTable($SchoolTypeId = null): Stage
    {
        $stage = new Stage('Stundentafel', 'Übersicht');
        $buttonList = '';
        if (($tblSchoolTypeList = School::useService()->getConsumerSchoolTypeCommonAll())) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if ($tblSchoolType->getId() == $SchoolTypeId) {
                    $buttonList .= new Standard(new Info(new Bold($tblSchoolType->getName())), '/Education/Lesson/StudentSubjectTable', new Edit(), array('SchoolTypeId' => $tblSchoolType->getId()));
                } else {
                    $buttonList .= new Standard(
                        $tblSchoolType->getName() . ($tblSchoolType->getShortName() == 'Gy' ? ' (SekI)' : '')
                        , '/Education/Lesson/StudentSubjectTable', null, array('SchoolTypeId' => $tblSchoolType->getId()));
                }
            }
        }

        $stage->setContent(
            ApiSubjectTable::receiverModal()
            . $buttonList
            . (($SchoolTypeId && ($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId)))
                ? new Title($tblSchoolType->getName())
                    . (new Primary('Eintrag hinzufügen', ApiSubjectTable::getEndpoint(), new Plus()))
                        ->ajaxPipelineOnClick(ApiSubjectTable::pipelineOpenCreateSubjectTableModal($SchoolTypeId))
                    . (new Primary('Verknüpfung hinzufügen', ApiSubjectTable::getEndpoint(), new Plus()))
                        ->ajaxPipelineOnClick(ApiSubjectTable::pipelineOpenCreateSubjectTableLinkModal($SchoolTypeId))
                    . new Container('&nbsp;')
                : '')
            . ApiSubjectTable::receiverBlock($this->loadSubjectTableContent($SchoolTypeId), 'SubjectTableContent')
        );

        return $stage;
    }

    /**
     * @param $SchoolTypeId
     *
     * @return string
     */
    public function loadSubjectTableContent($SchoolTypeId): string
    {
        if ($SchoolTypeId === null) {
            return new Container('&nbsp;') . new Warning('Bitte wählen Sie zunächst eine allgemeinbildende Schulart aus.');
        }

        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType))) {
                $dataList = array();
                $levelList = array();
                foreach ($tblSubjectTableList as $tblSubjectTable) {
                    $linkEdit = '';
                    if (($tblSubjectTableLink = DivisionCourse::useService()->getSubjectTableLinkBySubjectTable($tblSubjectTable))) {
                        $linkEdit = (new Link('', ApiSubjectTable::getEndpoint(), new LinkIcon(), array(), 'Verknüpfung bearbeiten'))
                            ->ajaxPipelineOnClick(ApiSubjectTable::pipelineOpenEditSubjectTableLinkModal($tblSubjectTableLink->getId(), $SchoolTypeId));
                    }

                    $subjectId = $tblSubjectTable->getSubjectId();
                    $levelList[$tblSubjectTable->getLevel()] = $tblSubjectTable->getLevel();
                    $dataList[$tblSubjectTable->getTypeName()][$tblSubjectTable->getRanking()][$subjectId]['Name'] = $tblSubjectTable->getSubjectName();
                    $dataList[$tblSubjectTable->getTypeName()][$tblSubjectTable->getRanking()][$subjectId]['Levels'][$tblSubjectTable->getLevel()]
                        = (new Link(($tblSubjectTable->getHoursPerWeek() === null ? '*' : $tblSubjectTable->getHoursPerWeek())
                            . (!$tblSubjectTable->getHasGrading() ? ' ' . new Ban() : ''), ApiSubjectTable::getEndpoint(),
                            null, array(), 'Eintrag bearbeiten'
                        ))->ajaxPipelineOnClick(ApiSubjectTable::pipelineOpenEditSubjectTableModal($tblSubjectTable->getId(), $SchoolTypeId))
                        . ($linkEdit ? '&nbsp;&nbsp;|&nbsp;&nbsp;' . $linkEdit : '');
                }

                if ($levelList) {
                    $countLevel = count($levelList);
                    $widthLevel = $countLevel < 5 ? 2 : 1;
                    $widthSubject = 12 - $countLevel * $widthLevel;

                    $content = $this->setContentByTypeName('Pflichtbereich', $dataList, $levelList, $widthSubject, $widthLevel);
                    $content .= $this->setContentByTypeName('Wahlpflichtbereich', $dataList, $levelList, $widthSubject, $widthLevel);
                    $content .= $this->setContentByTypeName('Wahlbereich', $dataList, $levelList, $widthSubject, $widthLevel);

                    return $content;
                }
            }
        } else {
            return new Danger('Schulart nicht gefunden', new Exclamation());
        }

        return '';
    }

    /**
     * @param $typeName
     * @param $dataList
     * @param $levelList
     * @param $widthSubject
     * @param $widthLevel
     *
     * @return string
     */
    private function setContentByTypeName($typeName, $dataList, $levelList, $widthSubject, $widthLevel): string
    {
        if (isset($dataList[$typeName])) {
            $titleColumns[] = new LayoutColumn(new Bold($typeName), $widthSubject);
            foreach ($levelList as $item) {
                $titleColumns[] = new LayoutColumn(new Bold($item), $widthLevel);
            }

            $contentList = array();
            ksort($dataList[$typeName]);
            foreach ($dataList[$typeName] as $rankingList) {
                foreach ($rankingList as $list) {
                    $columns = array();
                    $columns[] = new LayoutColumn($list['Name'], $widthSubject);
                    foreach ($levelList as $level) {
                        $columns[] = new LayoutColumn(isset($list['Levels'][$level]) ? $list['Levels'][$level] : '-', $widthLevel);
                    }

                    $contentList[] = new Layout(new LayoutGroup(new LayoutRow($columns)));
                }
            }

            return new Panel(
                new Layout(new LayoutGroup(new LayoutRow($titleColumns))),
                $contentList,
                Panel::PANEL_TYPE_INFO
            );
        }

        return '';
    }

    /**
     * @param $SubjectTableId
     * @param $SchoolTypeId
     * @param bool $setPost
     *
     * @return Form
     */
    public function formSubjectTable($SubjectTableId = null,$SchoolTypeId = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblSubjectTable = DivisionCourse::useService()->getSubjectTableById($SubjectTableId);
        if ($setPost && $tblSubjectTable) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Level'] = $tblSubjectTable->getLevel();
            $Global->POST['Data']['TypeName'] = $tblSubjectTable->getTypeName();
            $Global->POST['Data']['Subject'] = $tblSubjectTable->getSubjectId();
            $Global->POST['Data']['StudentMetaIdentifier'] = $tblSubjectTable->getStudentMetaIdentifier();
            $Global->POST['Data']['HoursPerWeek'] = $tblSubjectTable->getHoursPerWeek();
            $Global->POST['Data']['HasGrading'] = $tblSubjectTable->getHasGrading();
            $Global->savePost();
        } elseif (!$tblSubjectTable) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['TypeName'] = 'Pflichtbereich';
            $Global->POST['Data']['HasGrading'] = 1;
            $Global->savePost();
        }

        if ($SubjectTableId) {
            $buttonList[] = (new Primary('Speichern', ApiSubjectTable::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSubjectTable::pipelineEditSubjectTableSave($SubjectTableId, $SchoolTypeId));
            $buttonList[] = (new \SPHERE\Common\Frontend\Link\Repository\Danger('Löschen', ApiSubjectTable::getEndpoint(), new Remove()))
                ->ajaxPipelineOnClick(ApiSubjectTable::pipelineOpenDeleteSubjectTableModal($SubjectTableId, $SchoolTypeId));
        } else {
            $buttonList[] = (new Primary('Speichern', ApiSubjectTable::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiSubjectTable::pipelineCreateSubjectTableSave($SchoolTypeId));
        }

        if (!($tblSubjectList = Subject::useService()->getSubjectAll())) {
            $tblSubjectList = array();
        }
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_1_Id, 'FS1', '1. Fremdsprache (Schülerakte)');
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_2_Id, 'FS2', '2. Fremdsprache (Schülerakte)');
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_3_Id, 'FS3', '3. Fremdsprache (Schülerakte)');
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_FOREIGN_LANGUAGE_4_Id, 'FS4', '4. Fremdsprache (Schülerakte)');
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_RELIGION, 'R', 'Religion (Schülerakte)');
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_PROFILE, 'P', 'Profil (Schülerakte)');
        $tblSubjectList[] = TblSubject::withParameter(TblSubjectTable::SUBJECT_ORIENTATION, 'W', 'Wahlbereich (Schülerakte)');

        $typeNameList[] = new SelectBoxItem('Pflichtbereich', 'Pflichtbereich');
        $typeNameList[] = new SelectBoxItem('Wahlpflichtbereich', 'Wahlpflichtbereich');
        $typeNameList[] = new SelectBoxItem('Wahlbereich', 'Wahlbereich');

        $studentMetaList[] = new SelectBoxItem(0, '-[ Nicht ausgewählt ]-');
        $studentMetaList[] = new SelectBoxItem('RELIGION', 'Religion');
        $studentMetaList[] = new SelectBoxItem('PROFIL', 'Profil');
        $studentMetaList[] = new SelectBoxItem('ORIENTATION', 'Wahlbereich');
        $studentMetaList[] = new SelectBoxItem('ELECTIVE', 'Wahlfächer');

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new NumberField('Data[Level]', '', 'Klassenstufe'))->setRequired()
                        , 6),
                    new FormColumn(
                        (new SelectBox('Data[TypeName]', 'Typ', array('{{ Name }}' => $typeNameList)))->setRequired()
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        (new SelectBox('Data[Subject]', 'Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)))->setRequired()
                        , 6),
                    new FormColumn(
                        new SelectBox('Data[StudentMetaIdentifier]', 'Verknüpfung Schülerakte', array('{{ Name }}' => $studentMetaList))
                        , 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new NumberField('Data[HoursPerWeek]', '', 'Wochenstunden')
                        , 6),
                    new FormColumn(
                        new CheckBox('Data[HasGrading]', 'Benotung', 1)
                        , 6),
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
     * @param $SubjectTableLinkId
     * @param $SchoolTypeId
     * @param $Data
     * @param bool $setPost
     *
     * @return Form
     */
    public function formSubjectTableLink($SubjectTableLinkId = null, $SchoolTypeId = null, $Data = null, bool $setPost = false): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblSubjectTableLink = DivisionCourse::useService()->getSubjectTableLinkById($SubjectTableLinkId);
        if ($setPost && $tblSubjectTableLink) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Level'] = $tblSubjectTableLink->getTblSubjectTable()->getLevel();
            $Global->POST['Data']['MinCount'] = $tblSubjectTableLink->getMinCount();

            if (($tblSubjectTableLinkList = DivisionCourse::useService()->getSubjectTableLinkListByLinkId($tblSubjectTableLink->getLinkId()))) {
                foreach ($tblSubjectTableLinkList as $item) {
                    $Global->POST['Data']['SubjectTables'][$item->getTblSubjectTable()->getId()] = 1;
                }
            }

            $Global->savePost();
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Data[Level]', '', 'Klassenstufe'))
                            ->setRequired()
                            ->ajaxPipelineOnKeyUp(ApiSubjectTable::pipelineLoadCheckSubjectTableContent($SchoolTypeId, $SubjectTableLinkId))
                        , 6),
                    new FormColumn(
                        (new NumberField('Data[MinCount]', '', 'Mindestanzahl der verknüpften Fächer pro Schüler'))->setRequired()
                        , 6),
                )),
                new FormRow(new FormColumn(array(
                    ApiSubjectTable::receiverBlock($this->loadCheckSubjectTableContent($SchoolTypeId, $SubjectTableLinkId, $Data), 'CheckSubjectTableContent')
                ))),
            ))
        ))->disableSubmitAction();
    }

    /**
     * @param $SchoolTypeId
     * @param $SubjectTableLinkId
     * @param null $Data
     *
     * @return string
     */
    public function loadCheckSubjectTableContent($SchoolTypeId, $SubjectTableLinkId, $Data = null): string
    {
        if (($tblSchoolType = Type::useService()->getTypeById($SchoolTypeId))) {
            if (isset($Data['Level']) && $Data['Level'] !== '') {
                if (($tblSubjectTableList = DivisionCourse::useService()->getSubjectTableListBy($tblSchoolType, $Data['Level']))) {
                    $dataList = array();
                    foreach ($tblSubjectTableList as $tblSubjectTable) {
                        $dataList[] = new CheckBox('Data[SubjectTables][' . $tblSubjectTable->getId() . ']', $tblSubjectTable->getSubjectName(), 1);
                    }

                    return new Panel('Einträge verknüpfen', $dataList, Panel::PANEL_TYPE_INFO)
                        . ($SubjectTableLinkId
                            ? (new Primary('Speichern', ApiSubjectTable::getEndpoint(), new Save()))
                                ->ajaxPipelineOnClick(ApiSubjectTable::pipelineEditSubjectTableLinkSave($SubjectTableLinkId, $SchoolTypeId))
                            . (new \SPHERE\Common\Frontend\Link\Repository\Danger('Löschen', ApiSubjectTable::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(ApiSubjectTable::pipelineOpenDeleteSubjectTableLinkModal($SubjectTableLinkId, $SchoolTypeId))
                            : (new Primary('Speichern', ApiSubjectTable::getEndpoint(), new Save()))
                                ->ajaxPipelineOnClick(ApiSubjectTable::pipelineCreateSubjectTableLinkSave($SchoolTypeId))
                        );
                } else {
                    return new Warning('Für diese Klassenstufe gibt es noch keine Einträge in der Stundentafel.');
                }
            } else {
                return new Warning('Bitte geben Sie zunächst ein Klassenstufe an.');
            }
        }

        return new Danger('Schulart nicht gefunden', new Exclamation());
    }
}