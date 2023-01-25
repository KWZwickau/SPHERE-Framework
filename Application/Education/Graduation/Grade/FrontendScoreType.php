<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Equalizer;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\ToggleSelective;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Stage;

abstract class FrontendScoreType extends FrontendScoreRule
{
    /**
     * @return Stage
     */
    public function frontendScoreType(): Stage
    {
        $Stage = new Stage('Bewertungssystem', 'Übersicht');
        $Stage->setMessage(
            'Hier werden alle verfügbaren Bewertungssysteme angezeigt. Nach der Auswahl eines Bewertungssystems können dem
            Bewertungssystem die entsprechenden Fach-Klassenstufen zugeordnet werden.'
        );

        $dataList = array();
        if ($tblScoreTypeAll = Grade::useService()->getScoreTypeAll()) {
            foreach ($tblScoreTypeAll as $tblScoreType) {
                $dataList[] = array(
                    'Name' => $tblScoreType->getName(),
                    'Option' => new Standard('', '/Education/Graduation/Grade/ScoreType/Subject', new Equalizer(),
                        array('Id' => $tblScoreType->getId()), 'Fach-Klassenstufen zuordnen')
                );
            }
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData(
                                $dataList, null,
                                array(
                                    'Name' => 'Name',
                                    'Option' => ''
                                ),
                                array(
                                    'order' => array(
                                        array('0', 'asc'),
                                    ),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'width' => '30px', 'targets' => -1),
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage
     */
    public function frontendScoreTypeSubject($Id = null): Stage
    {
        $Stage = new Stage('Bewertungssystem', 'Klassenstufen(Schulart) einem Bewertungssystem zuordnen');
        $Stage->setMessage('Hier können dem ausgewählten Bewertungssystem Klassenstufen(Schulart) zugeordnet werden.');
        $Stage->addButton(new Standard('Zurück', '/Education/Graduation/Grade/ScoreType', new ChevronLeft()));

        if ($tblScoreType = Grade::useService()->getScoreTypeById($Id)) {
            $Stage->setContent(
                new Panel(
                    'Bewertungssystem',
                    new Bold($tblScoreType->getName()),
                    Panel::PANEL_TYPE_INFO
                )
                . new Well($this->formScoreType($tblScoreType))
            );
        } else {
            $Stage->setContent(new Danger('Bewertungssystem nicht gefunden.', new Exclamation()));
        }

        return $Stage;
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param null $Data
     *
     * @return Form
     */
    public function formScoreType(TblScoreType $tblScoreType, $Data = null): Form
    {
        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeCommonAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => $tblSchoolTypeList)))
                        ->ajaxPipelineOnChange(ApiScoreType::pipelineLoadScoreTypeSubjects($tblScoreType->getId()))
                    , 12),
            )),
            new FormRow(new FormColumn(
                ApiScoreType::receiverBlock($this->loadScoreTypeSubjects($tblScoreType, $Data), 'ScoreTypeSubjectsContent')
            )),
            new FormRow(new FormColumn(array(
                (new Primary('Speichern', ApiScoreType::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiScoreType::pipelineSaveScoreTypeEdit($tblScoreType->getId())),
                (new Standard('Abbrechen', '/Education/Graduation/Grade/ScoreType', new Disable()))
            )))
        )));
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreTypeSubjects(TblScoreType $tblScoreType, $Data = null): string
    {
        if ((isset($Data['SchoolType']) && ($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType'])))) {
            $list = array();
            if (($tblScoreTypeSubjectList = Grade::useService()->getScoreTypeSubjectListBySchoolType($tblSchoolType))) {
                $global = $this->getGlobal();
                foreach ($tblScoreTypeSubjectList as $tblScoreTypeSubject) {
                    if (($tblSubject = $tblScoreTypeSubject->getServiceTblSubject())
                        && ($tblScoreTypeTemp = $tblScoreTypeSubject->getTblScoreType())
                    ) {
                        if ($tblScoreType->getId() == $tblScoreTypeTemp->getId()) {
                            $global->POST['Data']['Subjects'][$tblScoreTypeSubject->getLevel()][$tblSubject->getId()] = 1;
                        } else {
                            $list[$tblScoreTypeSubject->getLevel()][$tblSubject->getId()] = ' ' . new Label($tblScoreTypeTemp->getName(), Label::LABEL_TYPE_PRIMARY);
                         }
                    }
                }
                $global->savePost();
            }

            $size = 3;
            $columnList = array();
            $toggleList = array();

            $minLevel = $tblSchoolType->getMinLevel();
            $maxLevel = $tblSchoolType->getMaxLevel();
            for ($level = $minLevel; $level <= $maxLevel; $level++) {
                $contentPanelList = array();
                if (($tblYearList = Term::useService()->getYearByNow())) {
                    foreach ($tblYearList as $tblYear) {
                        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListBySchoolTypeAndLevelAndYear($tblSchoolType, $level, $tblYear))) {
                            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName');
                            foreach ($tblSubjectList as $tblSubject) {
                                $name = 'Data[Subjects][' . $level . '][' . $tblSubject->getId() .']';
                                $toggleList[$level][$tblSubject->getId()] = $name;
                                $contentPanelList[$level][$tblSubject->getId()] =
                                    new CheckBox($name, $tblSubject->getDisplayName() . ($list[$level][$tblSubject->getId()] ?? ''), 1);
                            }
                        }
                    }
                }

                if (!empty($contentPanelList[$level])) {
                    if (isset($toggleList[$level])) {
                        array_unshift($contentPanelList[$level], new ToggleSelective('Alle wählen/abwählen', $toggleList[$level]));
                    }
                    $columnList[] = new LayoutColumn(new Panel('Klassenstufe ' . $level, $contentPanelList[$level], Panel::PANEL_TYPE_INFO), $size);
                }
            }

            if (empty($columnList)) {
                return new Warning('Keine entsprechenden Klassenstufen gefunden.', new Exclamation());
            } else {
                return new Layout(new LayoutGroup(
                    Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                    new Title($tblSchoolType->getName())
                ));
            }
        }

        return new Warning('Bitte wählen sie zunächst eine Schulart aus.');
    }
}