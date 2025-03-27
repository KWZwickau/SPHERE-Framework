<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreRuleBehavior;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Stage;

class FrontendScoreRuleBehavior extends FrontendScoreRule
{
    /**
     * @return Stage
     */
    public function frontendBehaviorScoreRule(): Stage
    {
        $Stage = new Stage('Berechnungsvorschrift Kopfnoten', 'Klassenstufen (Schulart) eine Gewichtung für Kopfnoten zuordnen');
        $Stage->setMessage('Hier kann für Kopfnoten eine Gewichtung eingestellt werden.'
            . new Container(new Bold('Alle Fächer für welche keine Gewichtung angegeben wird, werden mit 1 gewichtet!'))
            . new Container('Falls in der Stundentafel hinterlegt, werden die Wochenstunden (Wo.-h) für die Fächer in Klammern mit angegeben.')
        );

        $Stage->setContent(
            new Well($this->formScoreRuleBehavior())
        );

        return $Stage;
    }

    /**
     * @param null $Data
     *
     * @return Form
     */
    public function formScoreRuleBehavior($Data = null): Form
    {
        $tblSchoolTypeList = School::useService()->getConsumerSchoolTypeAll();

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => $tblSchoolTypeList)))
                        ->ajaxPipelineOnChange(ApiScoreRuleBehavior::pipelineLoadScoreRuleSubjects())
                    , 12),
            )),
            new FormRow(new FormColumn(
                ApiScoreRuleBehavior::receiverBlock($this->loadScoreRuleBehaviorSubjects($Data), 'ScoreRuleSubjectsContent')
            )),
            new FormRow(new FormColumn(array(
                (new Primary('Speichern', ApiScoreRuleBehavior::getEndpoint(), new Save()))
                    ->ajaxPipelineOnClick(ApiScoreRuleBehavior::pipelineSaveScoreRuleEdit()),
                (new Standard('Abbrechen', '/Education/Graduation/Grade/BehaviorScoreRule', new Disable()))
            )))
        )));
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function loadScoreRuleBehaviorSubjects($Data = null): string
    {
        $calcProposalBehaviorGrade = ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation', 'CalcProposalBehaviorGrade'))
            && $tblSetting->getValue();

        if ((isset($Data['SchoolType']) && ($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType'])))) {
            $exitsList = array();
            $global = $this->getGlobal();
            // Post zurücksetzen
            $global->POST['Data']['Subjects'] = array();
            if (($tblScoreRuleBehaviorSubjectList = Grade::useService()->getScoreRuleBehaviorSubjectListBySchoolType($tblSchoolType))) {
                foreach ($tblScoreRuleBehaviorSubjectList as $tblScoreTypeSubject) {
                    $subjectId = $tblScoreTypeSubject->getServiceTblSubject() ? $tblScoreTypeSubject->getServiceTblSubject()->getId() : 0;
                    $exitsList[$tblScoreTypeSubject->getLevel()][$subjectId] = $tblScoreTypeSubject->getMultiplier();
                    $global->POST['Data']['Subjects'][$tblScoreTypeSubject->getLevel()][$subjectId] = $tblScoreTypeSubject->getMultiplier();
                }
            }
            $global->savePost();

            $size = 4;
            $columnList = array();

            $minLevel = $tblSchoolType->getMinLevel();
            $maxLevel = $tblSchoolType->getMaxLevel();
            for ($level = $minLevel; $level <= $maxLevel; $level++) {
                $contentPanelList = array();
                if (($tblYearList = Term::useService()->getYearByNow())) {
                    foreach ($tblYearList as $tblYear) {
                        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListBySchoolTypeAndLevelAndYear($tblSchoolType, $level, $tblYear))) {
                            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName');
                            foreach ($tblSubjectList as $tblSubject) {
                                $contentPanelList[$level][$tblSubject->getId()] = $this->getSubjectRow($tblSchoolType, $level, $tblSubject, !isset($exitsList[$level][$tblSubject->getId()]));
                            }
                        }
                    }
                }

                if (!empty($contentPanelList[$level])) {
                    if ($calcProposalBehaviorGrade) {
                        array_unshift($contentPanelList[$level], $this->getSubjectRow($tblSchoolType, $level, null, !isset($exitsList[$level][0])));
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

    private function getSubjectRow(TblType $tblSchoolType, int $level, ?TblSubject $tblSubject, bool $isMuted): Layout
    {
        // Anzeige der Wochenstunden aus Stundentafel
        $hoursPerWeek = '';
        if ($tblSubject
            && ($tblSubjectTable = DivisionCourse::useService()->getSubjectTableBy($tblSchoolType, $level, $tblSubject))
            && ($hours = $tblSubjectTable->getHoursPerWeek())
        ) {
            $hoursPerWeek = new Container(' (' . $hours . ' Wo.-h)');
        }

        $name = 'Data[Subjects][' . $level . '][' . ($tblSubject ? $tblSubject->getId() : 0) . ']';
        $text = $tblSubject ? $tblSubject->getDisplayName() . $hoursPerWeek : 'Kursleiter';

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                $isMuted ? new Muted($text) : new Bold($text)
                , 9),
            new LayoutColumn(
                new TextField($name, 1, '')
                , 3)
        ))));
    }
}