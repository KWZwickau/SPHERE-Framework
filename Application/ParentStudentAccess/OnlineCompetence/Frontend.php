<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineCompetence;

use SPHERE\Application\Api\Education\Competence\ApiOnlineSkillRate;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     *
     * @noinspection PhpUnused
     */
    public function frontendOnlineCompetence(): Stage
    {
        $stage = new Stage('Kompetenzübersicht');

        if (($tblPersonList = OnlineCompetence::useService()->getPersonListAndSourceFromAccountBySession())) {
            $tblPerson = null;
//            $tblPersonList = [];
//            $tblPersonList[] = Person::useService()->getPersonById(1);
            if (count($tblPersonList) == 1) {
                $tblPerson = current($tblPersonList);
                $content = $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName();
            } else {
                $content = (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new SelectBox('Data[PersonId]', '', array('{{ FirstSecondName }} {{ LastName }}' => $tblPersonList)))
                        ->ajaxPipelineOnChange(ApiOnlineSkillRate::pipelineLoadStudentContent())
                )))))->disableSubmitAction();
            }

            $stage->setContent(
                new Panel('Schüler', $content, Panel::PANEL_TYPE_INFO)
                . ApiOnlineSkillRate::receiverBlock($this->loadStudentContent($tblPerson), 'StudentContent')
            );
        } else {
            $stage->setContent(new Warning('Keine entsprechenden Schüler vorhanden!', new Info()));
        }

        return $stage;
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return string
     */
    public function loadStudentContent(?TblPerson $tblPerson): string
    {
        if ($tblPerson) {
            $subjectList = [];
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndDate($tblPerson))
                && ($tblYear = $tblStudentEducation->getServiceTblYear())
                && ($tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear([$tblPerson], $tblYear))
            ) {
                $subjectList[] = new SelectBoxItem(-1, 'Fächerübergreifend');
                foreach ($tblSubjectList as $tblSubject) {
                    $subjectList[] = new SelectBoxItem($tblSubject->getId(), $tblSubject->getName());
                }
            }

            return
                new Panel(
                    'Fach',
                    (new Form(new FormGroup(new FormRow(new FormColumn(
                        (new SelectBox('Data[SubjectId]', '', array('{{ Name }}' => $subjectList)))
                            ->ajaxPipelineOnChange(ApiOnlineSkillRate::pipelineLoadSubjectContent($tblPerson->getId()))
                    )))))->disableSubmitAction(),
                    Panel::PANEL_TYPE_INFO
                )
                . ApiOnlineSkillRate::receiverBlock($this->loadSubjectContent($tblPerson, null), 'SubjectContent');
        }

        return new Warning('Bitte wählen Sie zunächst einen Schüler aus.', new Info());
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject|null $tblSubject
     * @param bool $IsOldYears
     * @param bool $IsInterdisciplinary
     *
     * @return string
     */
    public function loadSubjectContent(TblPerson $tblPerson, ?TblSubject $tblSubject, bool $IsOldYears = false, bool $IsInterdisciplinary = false): string
    {
        $tblStudentEducationList = [];
        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYearTemp) {
                if (($tempList = SkillRate::useService()->getStudentEducationList($tblPerson, $tblYearTemp, $IsOldYears))) {
                    $tblStudentEducationList = $tempList;
                    break;
                }
            }
        }

        $isFirstYear = true;
        if ($tblSubject || $IsInterdisciplinary) {
            $content = '';
            // sortierung erstmal nach bewertung
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblYear = $tblStudentEducation->getServiceTblYear())
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && ($level = $tblStudentEducation->getLevel()) !== null
                    && ($tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject))
                ) {
                    $pullRight = '';
                    if ($isFirstYear) {
                        $isFirstYear = false;

                        $pullRight = new PullRight(
                            (new Standard('Alte Schuljahre ' . ($IsOldYears ? 'ausblenden' : 'anzeigen'), ApiOnlineSkillRate::getEndpoint()))
                                ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadSubjectContent(
                                    $tblPerson->getId(), $IsOldYears ? 'false' : 'true', $IsInterdisciplinary ? -1 : $tblSubject->getId()))
                        );
                    }

                    $content .= new Title(
                        (new Container(
                            'Schuljahr: ' . $tblYear->getName()
                            . new Muted(new Small(' Klassenstufe: ' . $level . ' Schulart: ' . $tblSchoolType->getName()))
                            . $pullRight
                        ))->setStyle(['height: 28px;'])
                    );

                    // Anzeige der Kompetenzbereich inklusive Kompetenzen
                    $skillAreaList = SkillRate::useService()->setStudentSkillsForDisplay($tblStudentSkillList, $IsInterdisciplinary);
                    foreach ($skillAreaList as $array) {
                        foreach ($array['ScoreTypeList'] as $scoreType) {
                            if (count($scoreType['SkillList']) > 0) {
                                $content .= $this->getSkillAreaRow($scoreType['tblScoreType'], $array['Name']);
                                foreach ($scoreType['SkillList'] as $skill) {
                                    $text = $skill['SkillLevel'] ? new Muted($skill['SkillLevel']) . ' ' : '';
                                    $text .= $skill['Skill'];
                                    $content .= $this->getSkillRowPercent($scoreType['tblScoreType'], $text, $skill['Display'], $skill['Value']);
                                }
                            }
                        }
                    }
                }
            }

            return $content ?: new Warning('Es sind noch keine Kompetenzbewertungen erfolgt.', new Exclamation());
        }

        return new Warning('Bitte wählen Sie zunächst ein Fach aus.', new Info());
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param string $text
     * @param string $displayRate
     * @param float $percentValue
     * @param string $backgroundColor
     *
     * @return string
     */
    private function getSkillRowPercent(TblScoreType $tblScoreType, string $text, string $displayRate, float $percentValue, string $backgroundColor = '#D8EDF7')
        : string
    {
        $result = "<div class='competence-row'>";
        $result .= "<div class='competence-bar' style='width: $percentValue%; background-color: $backgroundColor;'></div>";

        // vertikale Linien setzen
        $tblScoreTypeItemList = $tblScoreType->getScoreTypeItems();
        $maxCount = count($tblScoreTypeItemList);
        if ($maxCount > 0) {
            $factor = 100 / $maxCount;
            for ($i = 1; $i < $maxCount; $i++) {
                $position = $i * $factor;
                $result .= "<div class='competence-marker' style='left: $position%'></div>";
            }
        }

        $result .= "    <div class='competence-label'>
                            <span>$text</span>
                            <span>$displayRate</span>
                        </div>
                    </div>";

        return $result;

//        return  "
//                    <div class='competence-row'>
//                        <div class='competence-bar' style='width: $percentValue%; background-color: $backgroundColor;'></div>
//                        <div class='competence-marker' style='left: 33%'></div>
//                        <div class='competence-marker' style='left: 67%'></div>
//                        <div class='competence-label'>
//                            <span>$text</span>
//                            <span>$percent</span>
//                        </div>
//                    </div>
//                ";
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param string $text
     * @param string $backgroundColor
     *
     * @return string
     */
    private function getSkillAreaRow(TblScoreType $tblScoreType, string $text, string $backgroundColor = '#31708f;'): string
    {
        $height = '42px';

        // stufen anzeigen
        $steps = "";
        if (($tblScoreTypeItemList = $tblScoreType->getScoreTypeItems(true))) {
            $height = '72px';
            // vertikale Linie
            $steps .= "<div class='competence-divider'></div>";
            $steps .= "<div class='competence-area-sublabel'>";
            foreach ($tblScoreTypeItemList as $tblScoreTypeItem) {
                $steps .= "<span>{$tblScoreTypeItem->getName()}</span>";
            }
            $steps .= "</div>";
        }

        $result = "<div class='competence-row' style='height: $height;'>
                        <div class='competence-bar' style='width: 100%; background-color: $backgroundColor;'></div>
                        <div class='competence-area-label' style='color: white;'>
                            <span>$text</span>";
        $result .= $steps;
        $result .= "    </div>
                    </div>";

        return $result;

//        return  "
//                    <div class='competence-row' style='height: 72px;'>
//                        <div class='competence-bar' style='width: 100%; background-color: $backgroundColor;'></div>
//                        <div class='competence-area-label' style='color: white;'>
//                            <span>$text</span>
//                            <div class='competence-divider'></div>
//                            <div class='competence-area-sublabel'>
//                                <span>wenig</span>
//                                <span>teilweise</span>
//                                <span>häufig</span>
//                            </div>
//                        </div>
//                    </div>
//                ";
    }
}