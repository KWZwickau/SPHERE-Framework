<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\Competence;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

abstract class SkillStyle extends Certificate
{
    /**
     * @param bool $isSample
     * @param bool $isBigLogo
     * @param bool $showIndividualLogo
     *
     * @return Slice
     */
    protected function getHead(bool $isSample, bool $isBigLogo = true, bool $showIndividualLogo = true): Slice
    {
        $isOS = false;
        if (($tblCertificate = $this->getCertificateEntity())
            && ($tblSchoolType = $tblCertificate->getServiceTblSchoolType())
            && $tblSchoolType->getShortName() == 'OS'
        ) {
            $isOS = true;
        }

        if ($showIndividualLogo) {
            $picturePath = $this->getUsedPicture($isOS);
            $individuallyLogoHeight = $this->getPictureHeight($isOS);
        } else {
            $picturePath = '';
            $individuallyLogoHeight = '66px';
        }

        if ($isBigLogo){
            $height = '100px';
            $paddingTop = '24px';
        } else {
            $height = '60px';
            $paddingTop = '14px';
        }

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element()), '39%')
                ->addElementColumn($isSample
                    ? (new Element\Sample())
                        ->styleTextSize('30px')
                    : (new Element())
                        ->setContent('&nbsp;')
                )
                ->addElementColumn($picturePath
                    ? (new Element\Image($picturePath, 'auto', $individuallyLogoHeight))
                        ->styleAlignRight()
                    : (new Element())
                        ->setContent('&nbsp;')
                , '39%')
            )
            ->stylePaddingTop($paddingTop)
            ->styleHeight($height)
        ;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array
     */
    protected function getSkillContent(TblPerson $tblPerson): array
    {
        $sliceList = [];

        // Fächer des Schülers
        if (($tblStudentEducation = $this->getTblStudentEducation())
            && ($tblYear = $tblStudentEducation->getServiceTblYear())
            && ($tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear([$tblPerson], $tblYear))
        ) {
            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
            // Fächerübergreifend
            $sliceList = array_merge($sliceList, $this->getSubject($tblPerson, $tblYear, null));
            foreach ($tblSubjectList as $tblSubject) {
                $sliceList = array_merge($sliceList, $this->getSubject($tblPerson, $tblYear, $tblSubject));
            }
        }

        return $sliceList;
    }

    private function getSubject(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject): array
    {
        $sliceList = [];
        $sliceList[] = (new Slice())
            ->styleMarginTop('20px')
            ->styleBorderTop()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addElement((new Element())
                ->setContent($tblSubject ? $tblSubject->getName(): 'Überfachliche Kompetenzen')
                ->styleTextSize('16px')
                ->stylePaddingTop('10px')
                ->stylePaddingBottom('10px')
                ->stylePaddingLeft('5px')
                ->styleTextBold()
                ->styleBorderBottom()
            );

        $tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject);
        $skillAreaList = SkillRate::useService()->setStudentSkillsForDisplay(
            $tblStudentSkillList,
            !$tblSubject
        );

        // Anzeige der Kompetenzbereich inklusive Kompetenzen
        foreach ($skillAreaList as $array) {
            foreach ($array['ScoreTypeList'] as $scoreType) {
                if (count($scoreType['SkillList']) > 0) {
                    //$content .= $this->getSkillAreaRow($scoreType['tblScoreType'], $array['Name']);
                    // todo bewertungssystem
                    $sliceList[] = $this->getSkillArea($array['Name']);
                    foreach ($scoreType['SkillList'] as $skill) {
                        $text = $skill['SkillLevel'] ? $skill['SkillLevel'] . ' ' : '';
                        $text .= $skill['Skill'];
                        $sliceList[] = $this->getSkill($text, $skill['Display'], $skill['Value']);
                    }
                }
            }
        }

        // todo Platz ermitteln -> bei SeitenUmbruch (Fach (Fortsetzung), Kompetenzbereich (Fortsetzung)
        // todo auch mal Claude, chatgpt oder Rene fragen

        return $sliceList;
    }

    private function getSkillArea(string $skillArea): Slice
    {
        $section = new Section();
        $section->addElementColumn((new Element())
            ->setContent($skillArea)
            ->styleTextBold()
            ->stylePaddingLeft('5px')
            ->styleBorderBottom()
            ->styleBorderLeft()
            ->styleBorderRight()
        );

        return (new Slice())->addSection($section);
    }

    /**
     * @param string $skill
     * @param string $display
     * @param string $value
     *
     * @return Slice
     */
    private function getSkill(string $skill, string $display, string $value): Slice
    {
        // todo zwischenstriche?
        $sectionSkill = new Section();
        $sectionSkill
            ->addElementColumn((new Element())
                ->setContent($skill)
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                , '60%');

        $elementLeft = new Element();
        $elementLeft
            ->setContent($display)
            ->styleTextSize('12px')
            ->stylePaddingLeft('5px')
            ->styleBackgroundColor('lightblue')
            ->styleBorderLeft();
        $elementRight = new Element();
        $elementRight
            ->setContent('&nbsp;')
            ->styleTextSize('12px')
            ->styleBorderLeft();

        if (strlen($skill) > 70) {
            $elementLeft->styleHeight('29.3px');
            $elementRight->styleHeight('29.3px');
        }
        $slicePercent = new Slice();
        $slicePercent
            ->addSection((new Section())
                ->addElementColumn($elementLeft, $value . '%')
                ->addElementColumn($elementRight)
            );

        $sectionSkill->addSliceColumn($slicePercent);

        return (new Slice())
            ->addSection($sectionSkill)
            ->styleBorderLeft()
            ->styleBorderBottom()
            ->styleBorderRight();
    }
}