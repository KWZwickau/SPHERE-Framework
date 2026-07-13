<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineCompetence;

use SPHERE\Application\Api\Education\Competence\ApiOnlineSkillRate;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
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
            ) {
                $subjectList = SkillRate::useFrontend()->getSubjectListForStudentOverview($tblPerson, $tblYear);
            }

            return
                new Panel(
                    'Fach',
                    (new Form(new FormGroup(new FormRow(new FormColumn(
                        (new SelectBox('Data[SubjectId]', '', array('{{ Name }}' => $subjectList), null, false, null))
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
     * @param bool $isOldYears
     * @param bool $isInterdisciplinary
     * @param bool $isAllSubjects
     *
     * @return string
     */
    public function loadSubjectContent(TblPerson $tblPerson, ?TblSubject $tblSubject, bool $isOldYears = false,
        bool $isInterdisciplinary = false, bool $isAllSubjects = false): string
    {
        $tblStudentEducationList = [];
        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYearTemp) {
                if (($tempList = SkillRate::useService()->getStudentEducationList($tblPerson, $tblYearTemp, $isOldYears))) {
                    $tblStudentEducationList = $tempList;
                    break;
                }
            }
        }

        $isFirstYear = true;
        if ($tblSubject || $isInterdisciplinary || $isAllSubjects) {
            $content = '';
            // sortierung erstmal nach bewertung
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                if (($tblYear = $tblStudentEducation->getServiceTblYear())
                    && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    && ($level = $tblStudentEducation->getLevel()) !== null
                ) {
                    if ($isAllSubjects) {
                        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear([$tblPerson], $tblYear))) {
                            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
                            // Fächerübergreifend
                            array_unshift($tblSubjectList, null);
                            foreach ($tblSubjectList as $tblSubjectTemp) {
                                $contentItem = $this->getSubjectContent($tblPerson, $tblYear, $tblSchoolType, $level, $tblSubjectTemp,
                                    $isFirstYear, $isOldYears, !$tblSubjectTemp, $isAllSubjects);
                                if ($contentItem) {
                                    // Fach anzeigen
                                    $content .= new Title($tblSubjectTemp ? $tblSubjectTemp->getName() : 'Fächerübergreifend');
                                    $content .= $contentItem;
                                }
                            }
                        }
                    } else {
                        $content .= $this->getSubjectContent($tblPerson, $tblYear, $tblSchoolType, $level, $tblSubject,
                            $isFirstYear, $isOldYears, $isInterdisciplinary, $isAllSubjects);
                    }
                }
            }

            return $content ?: new Warning('Es sind noch keine Kompetenzbewertungen erfolgt.', new Exclamation());
        }

        return new Warning('Bitte wählen Sie zunächst ein Fach aus.', new Info());
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param bool $isFirstYear
     * @param bool $isOldYears
     * @param bool $isInterdisciplinary
     * @param bool $isAllSubjects
     *
     * @return string
     */
    private function getSubjectContent(TblPerson $tblPerson, TblYear $tblYear, TblType $tblSchoolType, int $level,
        ?TblSubject $tblSubject, bool &$isFirstYear, bool $isOldYears, bool $isInterdisciplinary, bool $isAllSubjects): string
    {
        $content = '';
        if (($tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject))) {
            $pullRight = '';
            if ($isFirstYear) {
                $isFirstYear = false;

                $pullRight = new PullRight(
                    (new Standard('Alte Schuljahre ' . ($isOldYears ? 'ausblenden' : 'anzeigen'), ApiOnlineSkillRate::getEndpoint()))
                        ->ajaxPipelineOnClick(ApiOnlineSkillRate::pipelineLoadSubjectContent(
                            $tblPerson->getId(), $isOldYears ? 'false' : 'true', $isInterdisciplinary ? -1 : $tblSubject?->getId()))
                );
            }

            if (!$isAllSubjects) {
                $content .= new Title(
                    (new Container(
                        'Schuljahr: ' . $tblYear->getName()
                        . new Muted(new Small(' Klassenstufe: ' . $level . ' Schulart: ' . $tblSchoolType->getName()))
                        . $pullRight
                    ))->setStyle(['height: 28px;'])
                );
            }

            // Anzeige der Kompetenzbereich inklusive Kompetenzen
            $skillAreaList = SkillRate::useService()->setStudentSkillsForDisplay($tblStudentSkillList, $isInterdisciplinary);
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

        return $content;
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param string $text
     * @param string $displayRate
     * @param float $percentValue
     * @param string $backgroundColor
     *
     * @return string
     * @noinspection PhpSameParameterValueInspection
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
     * @noinspection PhpSameParameterValueInspection
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