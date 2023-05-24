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
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Document\Generator\Service\Entity\TblDocument;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;

/**
 * Class AbstractStudentCard
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
abstract class AbstractStudentCard extends AbstractDocument
{

    /**
     * @return int
     */
    abstract public function getTypeId();

    /**
     * @param array  $subjectPosition
     * @param int    $countSubjectColumns
     * @param int    $widthFirstColumns
     * @param int    $widthLastColumns
     * @param string $heightHeader
     * @param string $paddingLeftHeader
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $textSizeSmall
     * @param bool   $isSecondary
     *
     * @return array
     */
    protected function setGradeLayoutHeader(
        &$subjectPosition = array(),
        $countSubjectColumns = 18,
        $widthFirstColumns = 6,
        $widthLastColumns = 5,
        $heightHeader = '150px',
        $paddingLeftHeader = '-80px',
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $textSizeSmall = '9px',
        $isSecondary = false
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
                ->styleHeight('16.6px')
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
                ->setContent($this->setRotatedContend($text, ($isSecondary ? '-55px': '-40px'), $paddingLeftHeader))
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
                ->setContent($this->setRotatedContend($text, ($isSecondary ? '-55px': '-40px'), $paddingLeftHeader))
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

            // umbrüche (<br><wbr> etc.) erzeugen Fehler bei der Darstellung
            $text = str_replace('/', ' / ' ,$text);

            if ($isSecondary) {
                $paddingTop = '-67px';
                $paddingLeft = '-93px';
            } else {
                $paddingTop = '-53px';
                $paddingLeft = '-67px';
            }

            $element = (new Element())
                ->setContent($this->setRotatedContend($text, $paddingTop , $paddingLeft, '-40px'))
                ->styleHeight($heightHeader)
                ->styleTextSize(strlen($text) > 35 ? '6px' : $textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthString);
        }
        for ($i = 1; $i <= 4; $i++) {
            $text = '&nbsp;';
            switch ($i) {
                case 1: $text = 'Datum des Zeugnisses'; break;
                case 2: $text = 'Versetzungsvermerke'; break;
                case 3: $text = 'Versäumnisse'; break;
                case 4: $text = 'Signum des Lehrers'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text, ($isSecondary ? '-55px': '-40px'), $paddingLeftHeader))
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
     * @param int $typeId
     *
     * @param int $countSubjectColumns
     * @param int $countRows
     * @param bool|integer $breakRow
     * @param int $widthFirstColumns
     * @param int $widthLastColumns
     * @param string $heightRow
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $textSizeSmall
     * @param string $textSizeNormal
     * @return array
     */
    protected function setGradeLayoutBody(
        $subjectPosition = array(),
        $typeId = 0,
        $countSubjectColumns = 18,
        $countRows = 12,
        $breakRow = false,
        $widthFirstColumns = 6,
        $widthLastColumns = 5,
        $heightRow = '18px',
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $textSizeSmall = '8px',
        $textSizeNormal = '11px'
    ) {

        $countGradesTotal = $countSubjectColumns + 4;
        $widthFirstColumnsString = $widthFirstColumns . '%';
        $widthLastColumnsString = $widthLastColumns . '%';
        $width = (100 - 3 * $widthFirstColumns - 4 * $widthLastColumns) / $countGradesTotal;
        $widthString = $width . '%';
        $countTotalColumns = 3 + $countGradesTotal + 4;

        $tblGradeTypeList = Grade::useService()->getGradeTypeList(true);

        $sliceList = array();
        for ($j = 1; $j <= $countRows; $j++) {

            // Zwischenabstand
            if ($breakRow & $breakRow == $j) {
                $sliceBreak = (new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight('30px')
                        ->styleBorderTop($thicknessOutLines)
                    );
                $sliceList[] = $sliceBreak;
            }

            $slice = new Slice();
            $section = new Section();
            for ($i = 1; $i <= $countTotalColumns; $i++) {
                $content = '&nbsp;';
                $textSize = $textSizeNormal;
                $paddingLeft = '2px';
                $thicknessLeft = $thicknessInnerLines;
                $widthColumn = $widthString;
                $height = $heightRow;
                if ($height == '18px') {
                    $paddingTop = '4px';
                } else {
                    $paddingTop = '1px';
                }

                if ($i  == 1) {
                    $thicknessLeft = $thicknessOutLines;
                    $widthColumn = $widthFirstColumnsString;
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.Division is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.Division }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == 2) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthFirstColumnsString;
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.Year is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.Year }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                } elseif ($i == 3) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthFirstColumnsString;
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.HalfYear is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.HalfYear }}
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
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.BehaviorGrade["' . $acronym . '"] is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.BehaviorGrade["' . $acronym . '"] }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                } elseif ($i >= 8 && $i <= (3 + $countGradesTotal)) {
                    $thicknessLeft = $i == 8 ? $thicknessOutLines : $thicknessInnerLines;
                    $widthColumn = $widthString;
                    if (isset($subjectPosition[$i - 7])) {
                        $tblSubject = $subjectPosition[$i - 7];
                        $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.SubjectGrade["' . $tblSubject->getAcronym() . '"] is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.SubjectGrade["' . $tblSubject->getAcronym() . '"] }}
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
                    if ($height == '18px') {
                        $paddingTop = '7px';
                        $height = '15px';
                    } else {
                        $paddingTop = '2px';
                        $height = '14px';
                    }
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.CertificateDate is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.CertificateDate }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == $countGradesTotal + 5) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthLastColumnsString;
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.TransferRemark is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.TransferRemark }}
                            {% else %}
                                &nbsp;
                            {% endif %}';
                } elseif ($i == $countGradesTotal + 6) {
                    $thicknessLeft = $thicknessInnerLines;
                    $widthColumn = $widthLastColumnsString;
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.Absence is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.Absence }}
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
                    ->styleBorderTop($j % 2 == 1 ? $thicknessOutLines : $thicknessInnerLines)
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
     * @param string $paddingLeft
     *
     * @return string
     */
    protected function setRotatedContend($text = '&nbsp;', $paddingTop = '0px', $paddingLeft = '-90px', $paddingRight = '')
    {

        return
            '<div style="padding-top: ' . $paddingTop
            . '!important;padding-left: ' . $paddingLeft
            . ($paddingRight !== '' ? '!important; padding-right: ' . $paddingRight : '')
            . '!important;transform: rotate(-90deg)!important;">'
            . $text
            . '</div>';
    }

    /**
     * @param string $textSize
     *
     * @return Slice
     */
    protected function setLetterRow($textSize = '16px')
    {

        $countCharacters = 26;
        $width = (100/$countCharacters) . '%';
        $section = new Section();
        for ($i = 1; $i <= $countCharacters; $i++)
        {
            switch ($i){
                case 1: $character = 'A'; break;
                case 2: $character = 'B'; break;
                case 3: $character = 'C'; break;
                case 4: $character = 'D'; break;
                case 5: $character = 'E'; break;
                case 6: $character = 'F'; break;
                case 7: $character = 'G'; break;
                case 8: $character = 'H'; break;
                case 9: $character = 'I'; break;
                case 10: $character = 'J'; break;
                case 11: $character = 'K'; break;
                case 12: $character = 'L'; break;
                case 13: $character = 'M'; break;
                case 14: $character = 'N'; break;
                case 15: $character = 'O'; break;
                case 16: $character = 'P'; break;
                case 17: $character = 'Q'; break;
                case 18: $character = 'R'; break;
                case 19: $character = 'S'; break;
                case 20: $character = 'T'; break;
                case 21: $character = 'U'; break;
                case 22: $character = 'V'; break;
                case 23: $character = 'W'; break;
                case 24: $character = 'X'; break;
                case 25: $character = 'Y'; break;
                case 26: $character = 'Z'; break;
                default: $character ='';
            }
            $section
                ->addElementColumn(( new Element() )
                    ->setContent($character)
                    ->styleTextSize($textSize)
                    ->styleTextBold()
                    ->styleBorderRight($i == $countCharacters ? '0px': '0.5px')
                    ->styleAlignCenter()
                    , $width);
        }

        return ( new Slice() )
            ->addSection($section);
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
                        $pointer++;
                        return $this->getNextSubject($tblDocument, $tblSubjectList, $pointer);
                    }
                }
            }
        } else {
            $pointer++;
        }

        return false;
    }

    /**
     * @param TblType $tblType
     *
     * @return Page
     */
    public function buildRemarkPage(TblType $tblType = null)
    {
        $textSize = '11px';
        $textSizeSmall = '9px';
        $height = '15px';
        $thicknessOuterLines = '1.2px';
        $thicknessInnerLines = '0.5px';
        $widthFirstColumn = '15%';

        $tblType ? $typeId = $tblType->getId() : $typeId = 0;

        $sliceList = array();
        if (($tblPrepareStudentList = Generator::useService()->getPrepareStudentListForStudentCard($this->getTblPerson(), $tblType))){
            $count = 0;
            $countList = count($tblPrepareStudentList);
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                $count++;
                // offset = 100
                if ($count < 100
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                    && ($tblCertificateType = $tblCertificate->getTblCertificateType())
                    && $tblCertificateType->getIdentifier() == 'MID_TERM_COURSE'
                ) {
                    $count = 101;
                }
                $sliceList[] = (new Slice())
                    ->styleBorderLeft($thicknessOuterLines)
                    ->styleBorderRight($thicknessOuterLines)
                    ->styleBorderBottom($count == $countList ? $thicknessOuterLines : $thicknessInnerLines)
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(
                                '{% if(Content.Certificate.' . $typeId . '.Data' . $count . '.Division is not empty) %}
                                    {{ Content.Certificate.' . $typeId . '.Data' . $count . '.Division }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                            )
                            ->stylePaddingLeft()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleTextSize($textSizeSmall)
                            , '4%')
                        ->addElementColumn((new Element())
                            ->setContent(
                                '{% if(Content.Certificate.' . $typeId . '.Data' . $count . '.YearForRemark is not empty) %}
                                    {{ Content.Certificate.' . $typeId . '.Data' . $count . '.YearForRemark }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                            )
                            ->stylePaddingLeft()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleTextSize($textSizeSmall)
                            , '13%')
                        ->addElementColumn((new Element())
                            ->setContent(
                                '{% if(Content.Certificate.' . $typeId . '.Data' . $count . '.Remark is not empty) %}
                                    {{ Content.Certificate.' . $typeId . '.Data' . $count . '.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                            )
                            ->styleBorderLeft($thicknessInnerLines)
                            ->stylePaddingLeft()
                            ->stylePaddingTop()
                            ->stylePaddingBottom()
                            ->styleTextSize($textSizeSmall)
                        )
                    );
            }
        }

        return (new Page())
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOuterLines)
                ->styleBorderTop($thicknessOuterLines)
                ->styleBorderRight($thicknessOuterLines)
                ->styleBorderBottom($thicknessOuterLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight($height)
                        , '1%')
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleTextSize($textSize)
                        ->styleHeight($height)
                        , $widthFirstColumn)
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight($height)
                        , '1%')
                    ->addElementColumn((new Element())
                        ->setContent('Bemerkungen')
                        ->styleTextSize($textSize)
                        ->styleHeight($height)
                        ->stylePaddingLeft('5px')
                        ->styleBorderLeft($thicknessInnerLines)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight($height)
                        ->styleBorderTop($thicknessInnerLines)
                        , '1%')
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr')
                        ->styleTextSize($textSize)
                        ->styleHeight($height)
                        , $widthFirstColumn)
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight($height)
                        ->styleBorderTop($thicknessInnerLines)
                        , '1%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($textSize)
                        ->styleHeight('15.5px')
                        ->stylePaddingLeft('5px')
                        ->styleBorderLeft($thicknessInnerLines)
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight($height)
                        ->styleBorderTop($thicknessInnerLines)
                        , '1%')
                    ->addElementColumn((new Element())
                        ->setContent('Unterschrift')
                        ->styleTextSize($textSize)
                        ->styleHeight($height)
                        , $widthFirstColumn)
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleHeight($height)
                        ->styleBorderTop($thicknessInnerLines)
                        , '1%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize($textSize)
                        ->styleHeight('15.5px')
                        ->stylePaddingLeft('5px')
                        ->styleBorderLeft($thicknessInnerLines)
                    )
                )
                ->styleBackgroundColor('#EEE')
            )
            ->addSliceArray($sliceList);
    }
}