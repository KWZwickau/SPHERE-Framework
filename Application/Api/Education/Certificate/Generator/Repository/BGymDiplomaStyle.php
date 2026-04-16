<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

abstract class BGymDiplomaStyle extends BGymStyle
{
    protected array $advancedCourses = array();
    protected array $basicCourses = array();
    protected array $tblSubjectList = array();

    protected ?TblLeaveStudent $tblLeaveStudent = null;
    protected ?TblPerson $tblPerson = null;

    /**
     * @param string $workField
     * @param string $height
     * @param int $rangeFrom
     * @param int $rangeTo
     *
     * @return Slice
     */
    protected function getWorkFieldDiploma(string $workField, string $height, int $rangeFrom = 0, int $rangeTo = 0): Slice
    {
        $slice = (new Slice())
            ->addElement((new Element())
                ->setContent($workField ?: '&nbsp;')
                ->styleAlignCenter()
                ->styleMarginTop('8px')
            );

        $count = 0;
        if (($tblSubjectList = $this->getSubjectListByWorkField(str_replace(' (Fortsetzung)', '', $workField)))) {
            foreach ($tblSubjectList as $tblSubject) {
                if (isset($this->advancedCourses[$tblSubject->getId()])
                    || isset($this->basicCourses[$tblSubject->getId()])
                ) {
                    $count++;
                    if ($rangeFrom && $count < $rangeFrom) {
                        continue;
                    }
                    if ($rangeTo && $count > $rangeTo) {
                        continue;
                    }

                    $slice->addSection($this->getSubjectGradeLineDiploma($tblSubject, isset($this->advancedCourses[$tblSubject->getId()])));
                    $this->tblSubjectList[$tblSubject->getId()] = $tblSubject;
                }
            }
        }

        return $slice->styleHeight($height);
    }

    /**
     * @return Slice
     */
    protected function getChosenSubjectsDiploma(): Slice
    {
        $chosenSubjectList = array();
        foreach ($this->advancedCourses as $advancedCourse) {
            if (!isset($this->tblSubjectList[$advancedCourse->getId()])) {
                $chosenSubjectList[] = $advancedCourse;
            }
        }
        foreach ($this->basicCourses as $basicCourse) {
            if (!isset($this->tblSubjectList[$basicCourse->getId()])) {
                $chosenSubjectList[] = $basicCourse;
            }
        }

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Wahlbereich')
                ->styleTextBold()
                ->styleAlignCenter()
                ->styleMarginTop('7px')
            )
            ->addSection(isset($chosenSubjectList[0])
                ? $this->getSubjectGradeLineDiploma($chosenSubjectList[0], false)
                : (new Section)->addElementColumn((new Element())->setContent('&nbsp;'))
            )
            ->addSection(isset($chosenSubjectList[1])
                ? $this->getSubjectGradeLineDiploma($chosenSubjectList[1], false)
                : (new Section)->addElementColumn((new Element())->setContent('&nbsp;'))
            );
    }

    /**
     * @param TblSubject $tblSubject
     * @param bool $isAdvancedCourse
     *
     * @return Section
     */
    protected function getSubjectGradeLineDiploma(TblSubject $tblSubject, bool $isAdvancedCourse): Section
    {
        $widthSubject = 39;
        $widthSpace = 1;
        $widthGrade = 20;
        $widthPoints = (100 - $widthSubject - $widthGrade - 5 * $widthSpace) / 4;

        $grades = array(
            '12-1' => '&ndash;',
            '12-2' => '&ndash;',
            '13-1' => '&ndash;',
            '13-2' => '&ndash;',
        );

        $averageList = array();

        if ($this->tblLeaveStudent) {
            for ($level = 12; $level < 14; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblLeaveAdditionalGrade = Prepare::useService()->getLeaveAdditionalGradeBy(
                            $this->tblLeaveStudent,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        if ($tblLeaveAdditionalGrade->getGrade() !== null && $tblLeaveAdditionalGrade->getGrade() !== '') {
                            $averageList[] = $tblLeaveAdditionalGrade->getGrade();
                            $value = str_pad($tblLeaveAdditionalGrade->getGrade(), 2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = $value;
                        }
                    }
                }
            }
        } elseif (($tblPrepare = $this->getTblPrepareCertificate()) && ($tblPerson = $this->tblPerson)) {
            for ($level = 12; $level < 14; $level++) {
                for ($term = 1; $term < 3; $term++) {
                    $midTerm = $level . '-' . $term;
                    if (($tblPrepareAdditionalGradeType = Prepare::useService()->getPrepareAdditionalGradeTypeByIdentifier($midTerm))
                        && ($tblPrepareAdditionalGrade = Prepare::useService()->getPrepareAdditionalGradeBy(
                            $tblPrepare,
                            $tblPerson,
                            $tblSubject,
                            $tblPrepareAdditionalGradeType
                        ))
                    ) {
                        if ($tblPrepareAdditionalGrade->getGrade() !== null && $tblPrepareAdditionalGrade->getGrade() !== '') {
                            $averageList[] = $tblPrepareAdditionalGrade->getGrade();
                            $isSelected = $tblPrepareAdditionalGrade->isSelected();
                            $value = str_pad($tblPrepareAdditionalGrade->getGrade(), 2, 0, STR_PAD_LEFT);
                            $grades[$midTerm] = ($isSelected ? '' : '(') . $value . ($isSelected ? '' : ')');
                        }
                    }
                }
            }
        }

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($tblSubject->getName() . ($isAdvancedCourse ? ' (LF)' : ''))
                ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE)
                ->styleBorderBottom()
                ->stylePaddingTop('5px')
                ->stylePaddingBottom('4px')
                , $widthSubject . '%'
            )
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['12-1']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['12-2']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['13-1']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($grades['13-2']), $widthPoints . '%')
            ->addElementColumn((new Element())->setContent('&nbsp;'), $widthSpace . '%')
            ->addElementColumn($this->getElementPoints($this->getAverageTextByGradeList($averageList)), $widthGrade . '%')
            ;
    }

    /**
     * @param string $contentGrade
     *
     * @return Element
     */
    protected function getElementPoints(string $contentGrade): Element
    {
        return (new Element())
            ->setContent($contentGrade)
            ->styleAlignCenter()
            ->styleBackgroundColor(self::BACKGROUND)
            ->stylePaddingTop(self::PADDING_TOP_GRADE)
            ->stylePaddingBottom(self::PADDING_BOTTOM_GRADE)
            ->styleMarginTop(self::MARGIN_TOP_GRADE_LINE);
    }

    /**
     * @param $personId
     * @param string $marginTop
     *
     * @return Slice
     */
    protected function getSignPartBGymDiploma($personId, string $marginTop = '450px'): Slice
    {
        $leaderName = '&nbsp;';
        $leaderDescription = 'Vorsitzende/r';

        if ($this->getTblPrepareCertificate()
            && ($tblGenerateCertificate = $this->getTblPrepareCertificate()->getServiceTblGenerateCertificate())
        ) {

            if (($tblGenerateCertificateSettingLeader = Generate::useService()->getGenerateCertificateSettingBy($tblGenerateCertificate, 'Leader'))
                && ($tblPersonLeader = Person::useService()->getPersonById($tblGenerateCertificateSettingLeader->getValue()))
            ) {
                $leaderName = $tblPersonLeader->getFullName();
                if (($tblCommon = $tblPersonLeader->getCommon())
                    && ($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())
                    && ($tblGender = $tblCommonBirthDates->getTblCommonGender())
                ) {
                    if ($tblGender->getName() == 'Männlich') {
                        $leaderDescription = 'Vorsitzender';
                    } elseif ($tblGender->getName() == 'Weiblich') {
                        $leaderDescription = 'Vorsitzende';
                    }
                }
            }
        }

        return (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElementDiploma('{% if( Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElementDiploma('
                        {% if( Content.P' . $personId . '.Input.Date is not empty) %}
                            {{ Content.P' . $personId . '.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}
                    ')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ort')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Siegel')
                    ->styleTextColor('gray')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '20%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Datum')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn(
                    $this->getElementDiploma('&nbsp;')->styleMarginTop('30px')
                    , '35%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                )
                ->addElementColumn(
                    $this->getElementDiploma('&nbsp;')->styleMarginTop('30px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderDescription . ' des Prüfungsausschusses')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('
                        {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Description }}
                        {% else %}
                            Schulleiter/in
                        {% endif %}'
                    )
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($leaderName)
                    ->styleTextSize('11px')
                    ->stylePaddingTop()
                    ->styleAlignCenter()
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                            {{ Content.P' . $personId . '.Headmaster.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}'
                    )
                    ->styleTextSize('11px')
                    ->stylePaddingTop()
                    ->styleAlignCenter()
                    , '35%')
            );
    }

    /**
     * @param $personId
     * @param $diplomaName
     * @param $marginTop
     *
     * @return Slice
     */
    protected function getBGySignPartCopy($personId, $diplomaName, $marginTop = '25px'): Slice
    {
        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('{% if( Content.P' . $personId . '.Company.Address.City.Name is not empty) %}
                            {{ Content.P' . $personId . '.Company.Address.City.Name }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('{{ Content.P' . $personId . '.Input.Date }}')
                    ->styleAlignCenter()
                    ->styleBorderBottom('0.5px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Ort')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Siegel')
                    ->styleTextColor('gray')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '20%')
                ->addElementColumn((new Element())
                    , '5%')
                ->addElementColumn((new Element())
                    ->setContent('Datum')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('gez. ' . ($this->CopyCertificateData['Leader'] ?? ''))
                    ->styleAlignCenter()
                    ->styleMarginTop('40px')
                    ->styleBorderBottom('0.5px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('gez. ' . ($this->CopyCertificateData['HeadmasterOriginalName'] ?? ''))
                    ->styleAlignCenter()
                    ->styleMarginTop('40px')
                    ->styleBorderBottom('0.5px')
                    , '35%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Vorsitzende/r des Prüfungsausschusses')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Schulleiter/in')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '35%')
            );

        $this->setTechnicalCertifiedCopyStatement($slice, $personId, $diplomaName);

        return $slice;
    }
}