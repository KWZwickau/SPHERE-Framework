<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 09:13
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;

/**
 * Class AbstractStudentCard
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
abstract class AbstractStudentCard extends AbstractDocument
{

    /**
     * @param array $subjectPosition
     * @param int $countSubjectColumns
     * @param int $widthFirstColumns
     * @param int $widthLastColumns
     * @param string $heightHeader
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $textSizeSmall
     *
     * @return array
     */
    protected function setGradeLayoutHeader(
        &$subjectPosition = array(),
        $countSubjectColumns = 18,
        $widthFirstColumns = 6,
        $widthLastColumns = 5,
        $heightHeader = '100px',
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $textSizeSmall = '7px'
    ) {

        $countGradesTotal = $countSubjectColumns + 4;
        $widthFirstColumnsString = $widthFirstColumns . '%';
        $widthLastColumnsString = $widthLastColumns . '%';
        $width = (100 - 3 * $widthFirstColumns - 4 * $widthLastColumns) / $countGradesTotal;
        $widthString = $width . '%';

        $sliceList = array();

        // first row
        $slice = new Slice();
        $section = new Section();
        for ($i = 1; $i <= 3; $i++) {
            $element = (new Element())
                ->setContent('&nbsp;')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthFirstColumnsString);
        }
        for ($i = 1; $i <= 4; $i++) {
            $element = (new Element())
                ->setContent('&nbsp;')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthString);
        }
        $section
            ->addElementColumn((new Element())
                ->setContent('Leistungen in den einzelnen Fächern')
                ->styleAlignCenter()
                ->styleTextSize('12px')
                ->styleHeight('16.5px')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderBottom($thicknessInnerLines)
                , ($countSubjectColumns * $width). '%'
            );
        for ($i = 1; $i <= 4; $i++) {
            $element = (new Element())
                ->setContent('&nbsp;')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines)
                ->styleBorderRight($i == 4 ? $thicknessOutLines : '0px');

            $section->addElementColumn($element, $widthLastColumnsString);
        }
        $slice->addSection($section);
        $sliceList[] = $slice;

        // second row
        $slice = new Slice();
        $section = new Section();
        for ($i = 1; $i <= 3; $i++) {
            $text = '&nbsp;';
            switch ($i) {
                case 1: $text = 'Klasse'; break;
                case 2: $text = 'Schuljahr'; break;
                case 3: $text = 'Schulhalbjahr'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text, '10px'))
                ->styleHeight($heightHeader)
                ->styleTextSize($textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthFirstColumnsString);
        }
        for ($i = 1; $i <= 4; $i++) {
            $text = '&nbsp;';
            switch ($i) {
                case 1: $text = 'Betragen'; break;
                case 2: $text = 'Fleiß'; break;
                case 3: $text = 'Mitarbeit'; break;
                case 4: $text = 'Ordnung'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text))
                ->styleHeight($heightHeader)
                ->styleTextSize($textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthString);
        }
        if ($this->getTblPerson()) {
            $tblSubjectList = Generator::useService()->getStudentCardSubjectListByPerson($this->getTblPerson(), $this);
        } else {
            $tblSubjectList = false;
        }
        $tblDocument = Generator::useService()->getDocumentByName($this->getName());
        $pointer = 1;
        for ($i = 1; $i <= $countSubjectColumns; $i++) {
            if (($tblSubject = $this->getNextSubject($tblDocument, $tblSubjectList, $pointer))) {
                $text = $tblSubject->getName();
                $subjectPosition[$i] = $tblSubject;
            } else {
                $text = '&nbsp;';
            }

            $element = (new Element())
                ->setContent($this->setRotatedContend($text))
                ->styleHeight($heightHeader)
                ->styleTextSize($textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthString);
        }
        for ($i = 1; $i <= 4; $i++) {
            $text = '&nbsp;';
            switch ($i) {
                case 1: $text = 'Datum des Zeugnisses'; break;
                case 2: $text = 'Versetzungsvermerke'; break;
                case 3: $text = 'Versäumnisse'; break;
                case 4: $text = 'Signums des Lehrers'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text, '4px'))
                ->styleHeight($heightHeader)
                ->styleTextSize($textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines)
                ->styleBorderRight($i == 4 ? $thicknessOutLines : '0px');

            $section->addElementColumn($element, $widthLastColumnsString);
        }
        $slice->addSection($section);
        $sliceList[] = $slice;

        return $sliceList;
    }

    /**
     * @param array $subjectPosition
     * @param int $countSubjectColumns
     * @param int $countRows
     * @param int $widthFirstColumns
     * @param int $widthLastColumns
     * @param string $heightRow
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $textSizeSmall
     * @param string $textSizeNormal
     *
     * @return array
     */
    protected function setGradeLayoutBody(
        $subjectPosition = array(),
        $countSubjectColumns = 18,
        $countRows = 12,
        $widthFirstColumns = 6,
        $widthLastColumns = 5,
        $heightRow = '18px',
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $textSizeSmall = '7px',
        $textSizeNormal = '11px'
    ) {

        $countGradesTotal = $countSubjectColumns + 4;
        $widthFirstColumnsString = $widthFirstColumns . '%';
        $widthLastColumnsString = $widthLastColumns . '%';
        $width = (100 - 3 * $widthFirstColumns - 4 * $widthLastColumns) / $countGradesTotal;
        $widthString = $width . '%';
        $countTotalColumns = 3 + $countGradesTotal + 4;

        $tblGradeTypeList = Gradebook::useService()->getGradeTypeAllByTestType(Evaluation::useService()->getTestTypeByIdentifier('BEHAVIOR'));

        $sliceList = array();
        for ($j = 1; $j <= $countRows; $j++) {
            $slice = new Slice();
            $section = new Section();
            for ($i = 1; $i <= $countTotalColumns; $i++) {
                $content = '&nbsp;';
                $textSize = $textSizeNormal;
                $paddingTop = '4px';
                $paddingLeft = '2px';
                $thicknessLeft = $thicknessInnerLines;
                $widthColumn = $widthString;
                $height = $heightRow;

                if ($i  == 1) {
                    $thicknessLeft = $thicknessOutLines;
                    $widthColumn = $widthFirstColumnsString;
                    $content = '{% if(Content.Certificate.Data' . $j . '.Division is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.Division }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == 2) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthFirstColumnsString;
                    $content = '{% if(Content.Certificate.Data' . $j . '.Year is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.Year }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                } elseif ($i == 3) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthFirstColumnsString;
                    $content = '{% if(Content.Certificate.Data' . $j . '.HalfYear is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.HalfYear }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                } elseif ($i >= 4 && $i <= 7) {
                    $acronym = '';
                    if (isset($tblGradeTypeList[$i - 4])
                        && ($tblGradeType = $tblGradeTypeList[$i - 4])
                    ) {
                       $acronym = $tblGradeType->getCode();
                    }
                    $thicknessLeft = $i == 4 ? $thicknessOutLines : $thicknessInnerLines;
                    $widthColumn = $widthString;
                    $content = '{% if(Content.Certificate.Data' . $j . '.BehaviorGrade.' . $acronym . ' is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.BehaviorGrade.' . $acronym . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                } elseif ($i >= 8 && $i <= (3 + $countGradesTotal)) {
                    $thicknessLeft = $i == 8 ? $thicknessOutLines : $thicknessInnerLines;
                    $widthColumn = $widthString;
//                    if ($tblDocument
//                        && ($tblDocumentSubject = Generator::useService()->getDocumentSubjectByDocumentAndRanking($tblDocument, $i - 7))
//                        && ($tblSubject = $tblDocumentSubject->getServiceTblSubject())
//                    ) {
//                        $content = '{% if(Content.Certificate.Data' . $j . '.SubjectGrade.' . $tblSubject->getAcronym() . ' is not empty) %}
//                                {{ Content.Certificate.Data' . $j . '.SubjectGrade.' . $tblSubject->getAcronym() . ' }}
//                            {% else %}
//                                 &nbsp;
//                            {% endif %}';
//                    } else {
//                        $content = '&nbsp;';
//                    }
                    if (isset($subjectPosition[$i - 7])) {
                        $tblSubject = $subjectPosition[$i - 7];
                        $content = '{% if(Content.Certificate.Data' . $j . '.SubjectGrade.' . $tblSubject->getAcronym() . ' is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.SubjectGrade.' . $tblSubject->getAcronym() . ' }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                    } else {
                        $content = '&nbsp;';
                    }
                } elseif ($i == $countGradesTotal + 4) {
                    $thicknessLeft = $thicknessOutLines;
                    $widthColumn = $widthLastColumnsString;
                    $textSize = $textSizeSmall;
                    $paddingLeft = '1px';
                    $paddingTop = '7px';
                    $height = '15px';
                    $content = '{% if(Content.Certificate.Data' . $j . '.CertificateDate is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.CertificateDate }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == $countGradesTotal + 5) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthLastColumnsString;
                    $content = '{% if(Content.Certificate.Data' . $j . '.TransferRemark is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.TransferRemark }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == $countGradesTotal + 6) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthLastColumnsString;
                    $content = '{% if(Content.Certificate.Data' . $j . '.Absence is not empty) %}
                                {{ Content.Certificate.Data' . $j . '.Absence }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == $countGradesTotal + 7) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthLastColumnsString;
                    $content = '&nbsp;';
                }

                $element = (new Element())
                    ->setContent($content)
                    ->styleHeight($height)
                    ->styleTextSize($textSize)
                    ->styleAlignCenter()
                    ->stylePaddingLeft($paddingLeft)
                    ->stylePaddingTop($paddingTop)
                    ->stylePaddingBottom('0px')
                    ->styleBorderLeft($thicknessLeft)
                    ->styleBorderTop($j == 1 ? $thicknessOutLines : $thicknessInnerLines)
                    ->styleBorderRight($i == $countTotalColumns ? $thicknessOutLines: '0px')
                    ->styleBorderBottom($j == $countRows ? $thicknessOutLines: '0px');
                $section->addElementColumn($element, $widthColumn);
            }
            $slice->addSection($section);
            $sliceList[] = $slice;
        }

        return $sliceList;
    }
    /**
     * @param string $text
     * @param string $paddingTop
     *
     * @return string
     */
    private function setRotatedContend($text = '&nbsp;', $paddingTop = '0px')
    {

        return
            '<div style="padding-top: ' . $paddingTop . '!important;padding-left: -90px!important;transform: rotate(270deg)!important;">'
            . $text
            . '</div>';
    }

    /**
     * @param string $textSize
     *
     * @return Slice
     */
    protected function setLetterRow($textSize = '18px')
    {

        return ( new Slice() )
            ->addSection(( new Section() )
                ->addElementColumn(( new Element() )
                    ->setContent('A')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('B')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('C')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('D')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('E')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('F')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('G')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('H')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('I')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('J')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('K')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('L')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('N')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('M')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('O')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('P')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('Q')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('R')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('S')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('T')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('U')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('V')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('W')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('XY')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
                ->addElementColumn(( new Element() )
                    ->setContent('Z')
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleAlignCenter()
                    , '4%')
            );
    }

    /**
     * @param TblDocument|false $tblDocument
     * @param TblSubject[]|false $tblSubjectList
     * @param integer $pointer
     *
     * @return TblSubject|false
     */
    protected function getNextSubject($tblDocument, $tblSubjectList, &$pointer)
    {
        if ($pointer > 30) {
            return false;
        }

        if ($tblDocument
            && ($tblDocumentSubject = Generator::useService()->getDocumentSubjectByDocumentAndRanking($tblDocument,
                $pointer))
        ) {
            if (($tblSubject = $tblDocumentSubject->getServiceTblSubject())) {
                if ($tblDocumentSubject->isEssential()) {
                    $pointer++;
                    return $tblSubject;
                } else {
                    // hat Schüler eine Note in diesem Fach?
                    if ($tblSubjectList
                        && isset($tblSubjectList[$tblSubject->getId()])
                    ) {
                        $pointer++;
                        return $tblSubject;
                    } else {
                        return $this->getNextSubject($tblDocument, $tblSubjectList, ++$pointer);
                    }
                }
            }
        } else {
            $pointer++;
        }

        return false;
    }
}