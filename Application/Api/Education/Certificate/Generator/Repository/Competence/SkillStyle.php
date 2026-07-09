<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\Competence;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

abstract class SkillStyle extends Certificate
{
    const int LEFT_WIDTH = 60;

    const int HEIGHT_SUBJECT = 70;
    const int HEIGHT_SKILL_AREA = 16;
    const int HEIGHT_SKILL = 16;
    const int HEIGHT_SKILL_TWO_ROW = 30;

    const int HEIGHT_PAGE = 1123 - (2 * 51);
    const int HEIGHT_HEADER = 40;

    protected int $heightStartPixel = 0;
    protected int $pageCount = 0;
    protected array $pageSliceList = [];
    protected ?TblSubject $tblLastSubject = null;
    protected ?string $lastSubjectArea = null;
    protected ?TblScoreType $tblScoreType = null;

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
     * @param string $certificateName
     *
     * @return array
     */
    protected function preBuildPages(TblPerson $tblPerson, string $certificateName): array
    {
        $pageList = [];
        foreach ($this->pageSliceList as $i => $pageSlices) {
            $page = new Page();
            // Kopfzeile
            if ($i > 1) {
                $page->addSlice((new Slice)
                    ->addElement((new Element())
                        ->setContent(
                            $certificateName . ' für '
                            . $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName()
//                            . ', geboren am {{ Content.P' . $tblPerson->getId() . '.Person.Common.BirthDates.Birthday }}'
                            . ' - ' . $i . '. Seite von '
                            . $this->pageCount . ' Seiten'
                        )
                    )
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->styleBorderBottom('0.5px')
                    ->styleMarginBottom('20px')
                );
            }

            $page->addSliceArray($pageSlices);
            $pageList[] = $page;
        }

        // leere Seite bei ungerader Anzahl für Massendruck anfügen
        if ($this->pageCount % 2 != 0) {
            $pageList[] = new Page();
        }

        return $pageList;
    }

    /**
     * @param TblPerson $tblPerson
     */
    protected function setSkillContent(TblPerson $tblPerson): void
    {
        // Fächer des Schülers
        if (($tblStudentEducation = $this->getTblStudentEducation())
            && ($tblYear = $tblStudentEducation->getServiceTblYear())
            && ($tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear([$tblPerson], $tblYear))
        ) {
            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('Name');
            // Fächerübergreifend
            $this->getSubject($tblPerson, $tblYear, null);
            foreach ($tblSubjectList as $tblSubject) {
                $this->getSubject($tblPerson, $tblYear, $tblSubject);
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     *
     * @return void
     */
    private function getSubject(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject): void
    {
        // Fach nur anzeigen, wenn auch Kompetenzen bewertet
        if (!($tblStudentSkillList = SkillRate::useService()->getStudentSkillListByPersonAndYear($tblPerson, $tblYear, $tblSubject))) {
            return;
        }

        $skillAreaList = SkillRate::useService()->setStudentSkillsForDisplay(
            $tblStudentSkillList,
            !$tblSubject
        );

        $slice = $this->getSubjectSlice($tblSubject, false);
        // prüfen, ob neue Seite erforderlich
        $this->checkNewPage(self::HEIGHT_SKILL_AREA + self::HEIGHT_SKILL_TWO_ROW);
        $this->pageSliceList[$this->pageCount][] = $slice;
        $this->tblLastSubject = $tblSubject;
        $this->lastSubjectArea = null;

        // Anzeige der Kompetenzbereich inklusive Kompetenzen
        foreach ($skillAreaList as $array) {
            foreach ($array['ScoreTypeList'] as $scoreType) {
                if (count($scoreType['SkillList']) > 0) {
                    $this->tblScoreType = $scoreType['tblScoreType'];
                    $slice = $this->getSkillAreaSlice($array['Name'], false);
                    // prüfen, ob neue Seite erforderlich
                    $this->checkNewPage(self::HEIGHT_SKILL_TWO_ROW);
                    $this->lastSubjectArea = $array['Name'];
                    $this->pageSliceList[$this->pageCount][] = $slice;

                    foreach ($scoreType['SkillList'] as $skill) {
                        $text = $skill['SkillLevel'] ? $skill['SkillLevel'] . ' ' : '';
                        $text .= $skill['Skill'];

                        $slice  = $this->getSkill($text, $skill['Display'], $skill['Value']);
                        // prüfen, ob neue Seite erforderlich
                        $this->checkNewPage(0);
                        $this->pageSliceList[$this->pageCount][] = $slice;
                    }
                    $this->lastSubjectArea = null;
                    $this->tblScoreType = null;
                }
            }
        }

        $this->tblLastSubject = null;
    }

    /**
     * @param int $minHeightRequiredAfter
     *
     * @return void
     */
    protected function checkNewPage(int $minHeightRequiredAfter): void
    {
        // prüfen bei Fach ob noch Platz zusätzlich skillArea + 2rowSkill
        // prüfen bei Kompetenzbereich ob noch Platz zusätzlich 2rowSkill
        if ($this->heightStartPixel > self::HEIGHT_PAGE - $minHeightRequiredAfter) {
            $this->heightStartPixel = self::HEIGHT_HEADER;
            $this->pageSliceList[++$this->pageCount] = [];
            if ($this->tblLastSubject !== null) {
                $this->pageSliceList[$this->pageCount][] = $this->getSubjectSlice($this->tblLastSubject, true)->styleBorderTop();
            }
            if ($this->lastSubjectArea !== null) {
                $this->pageSliceList[$this->pageCount][] = $this->getSkillAreaSlice($this->lastSubjectArea, true);
            }
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     * @param bool $isContinue
     *
     * @return Slice
     */
    public function getSubjectSlice(?TblSubject $tblSubject, bool $isContinue): Slice
    {
        $this->heightStartPixel += self::HEIGHT_SUBJECT;

        return (new Slice())
            ->styleMarginTop('20px')
            ->styleBorderTop()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->addElement((new Element())
                ->setContent(
                    ($tblSubject ? $tblSubject->getName(): 'Überfachliche Kompetenzen')
                    . ($isContinue ? ' (Fortsetzung)' : '')
                )
                ->styleTextSize('16px')
                ->stylePaddingTop('10px')
                ->stylePaddingBottom('10px')
                ->stylePaddingLeft('5px')
                ->styleTextBold()
                ->styleBorderBottom()
            );
    }

    /**
     * @param string $skillArea
     * @param bool $isContinue
     *
     * @return Slice
     */
    private function getSkillAreaSlice(string $skillArea, bool $isContinue): Slice
    {
        $this->heightStartPixel += self::HEIGHT_SKILL_AREA;

        $sliceScoreType = null;
        if ($this->tblScoreType
            && ($tblScoreTypeItemList = $this->tblScoreType->getScoreTypeItems(true))
        ) {
            $countItems = count($tblScoreTypeItemList);
            $sectionScoreType = new Section();
            $count = 0;
            foreach ($tblScoreTypeItemList as $tblScoreTypeItem) {
                $count++;
                $element = (new Element())
                    ->setContent($tblScoreTypeItem->getName())
                    ->styleAlignCenter()
                    ->styleBorderRight()
                    ->stylePaddingTop('3.25px')
                    ->stylePaddingBottom('3.25px')
                    ->styleTextSize('8.5px');
                if ($count == 1) {
                    $element->styleBorderLeft();
                }

                $sectionScoreType->addElementColumn($element, $count == $countItems ? 'auto' : 100 / $countItems . '%');
            }
            $sliceScoreType = (new Slice())->addSection($sectionScoreType);
        }

        $section = new Section();
        $section->addElementColumn((new Element())
            ->setContent($skillArea . ($isContinue ? ' (Fortsetzung)' : ''))
            ->styleTextBold()
            ->stylePaddingLeft('5px')
            ->styleBorderLeft()
        , $sliceScoreType ? self::LEFT_WIDTH .  '%' : '100%');
        if ($sliceScoreType) {
            $section->addSliceColumn($sliceScoreType);
        }

        $slice = (new Slice())
            ->addSection($section)
            ->styleBorderBottom();
        if (!$sliceScoreType) {
            $slice->styleBorderRight();
        }

        return $slice;
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
        // 2 Zeilen
        if (strlen($skill) > 70) {
            $height = '29.3px';

            $this->heightStartPixel += self::HEIGHT_SKILL_TWO_ROW;
            $marginTopText = '-20px';
            $marginTopLinie = '-29.3px';
        } else {
            $height = '17px';
            $this->heightStartPixel += self::HEIGHT_SKILL;
            $marginTopText = '-14px';
            $marginTopLinie = '-17px';
        }

        $sectionSkill = new Section();
        $sectionSkill
            ->addElementColumn((new Element())
                ->setContent($skill)
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                , self::LEFT_WIDTH .  '%');

        $elementLeft = new Element();
        $elementLeft
            ->setContent('&nbsp;')
            ->styleTextSize('12px')
            ->styleHeight($height)
            ->stylePaddingLeft('5px')
            ->styleBackgroundColor('lightblue')
            ->styleBorderLeft();
        $elementRight = new Element();
        $elementRight
            ->setContent('&nbsp;')
            ->styleTextSize('12px')
            ->styleHeight($height)
            ->styleBorderLeft();

        $sectionPercent = (new Section())
            ->addElementColumn($elementLeft, $value . '%');
        if ($value < 100) {
            $sectionPercent->addElementColumn($elementRight);
        }
        $sectionSkill->addSliceColumn((new Slice())->addSection($sectionPercent));
        $slice = (new Slice())
            ->addSection($sectionSkill);

        // Zwischenstriche
        if ($this->tblScoreType
            && ($tblScoreTypeItemList = $this->tblScoreType->getScoreTypeItems(true))
        ) {
            $countItems = count($tblScoreTypeItemList);
            $sectionScoreType = new Section();
            $count = 0;
            foreach ($tblScoreTypeItemList as $ignored) {
                $count++;
                $element = (new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight($height);
                if ($count < $countItems) {
                    $element->styleBorderRight('0.5px', 'silver');
                }

                $sectionScoreType->addElementColumn($element, $count == $countItems ? 'auto' : 100 / $countItems . '%');
            }
            $sliceScoreType = (new Slice())
                ->addSection($sectionScoreType)
                ->styleMarginTop($marginTopLinie)
                ->styleHeight('0px');
            $slice
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('0px')
                        ->styleMarginTop($marginTopLinie)
                        , self::LEFT_WIDTH .  '%')
                    ->addSliceColumn(
                        $sliceScoreType
                    )
                );
        }

        return $slice
            // anzeige des Wertes mit höhe 0 und danach damit dieser nicht vom Balken überdeckt wird
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleTextSize('10px')
                    ->styleHeight('0px')
                    ->styleMarginTop($marginTopText)
                    , self::LEFT_WIDTH .  '%')
                ->addElementColumn((new Element())
                    ->setContent($display)
                    ->stylePaddingLeft('5px')
                    ->styleTextSize('10px')
                    ->styleHeight('0px')
                    ->styleMarginTop($marginTopText)
                )
            )
            ->styleBorderLeft()
            ->styleBorderBottom()
            ->styleBorderRight();
    }
}