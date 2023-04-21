<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 10.03.2017
 * Time: 10:36
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;

/**
 * Class GrammarSchool
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentCard
 */
class GrammarSchool extends AbstractStudentCard
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerkartei - Gymnasium';
    }

    /**
     * @return int
     */
    public function getTypeId()
    {

        if (($tblType = Type::useService()->getTypeByName('Gymnasium'))) {
            return $tblType->getId();
        } else {
            return 0;
        }
    }

    /**
     * @return false|TblType
     */
    public function getType()
    {

        return Type::useService()->getTypeByName('Gymnasium');
    }

    /**
     * @return Page
     */
    public function buildPage()
    {

        $SmallTextSize = '7px';
        $InputText = '12px';
        $thicknessOutLines = '1.2px';
        $thicknessInnerLines = '0.5px';

        $subjectPosition = array();
        $subjectPositionSekII = array();

        return (new Page())
            ->addSlice($this->setLetterRow())
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderRight($thicknessOutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Gymnasium')
                        ->styleHeight('30px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                        ->stylePaddingTop('7px')
                        ->stylePaddingLeft('5px')
                        ->styleBorderRight($thicknessInnerLines)
                        , '18%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Name')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('Vorname')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('
                                                {% if( Content.Person.Data.Name.Last is not empty) %}
                                                    {{ Content.Person.Data.Name.Last }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('4px')
                                ->styleTextSize($InputText)
                                ->styleHeight('24.5px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                                {% if( Content.Person.Data.Name.First is not empty) %}
                                                    {{ Content.Person.Data.Name.First }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('4px')
                                ->styleTextSize($InputText)
                                ->styleHeight('24.5px')
                                , '40%')
                        )
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderTop($thicknessInnerLines)
                ->styleBorderRight($thicknessOutLines)
                ->styleBorderBottom($thicknessOutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Sekundarstufe I')
                        ->styleTextSize('12px')
                        ->styleHeight('16.5px')
                        ->stylePaddingTop('4px')
                        ->stylePaddingBottom('4px')
                        ->stylePaddingLeft('4px')
                        ->styleTextBold()
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            'Besuchtes Profil:'
                            . '{% if(Content.Student.Profile is not empty) %}
                                    {{ Content.Student.Profile }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                        )
                        ->styleTextSize('12px')
                        ->styleHeight('16.5px')
                        ->stylePaddingTop('4px')
                        ->stylePaddingBottom('4px')
                        ->stylePaddingLeft('4px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->styleHeight('8px')
                )
            )
            ->addSliceArray($this->setGradeLayoutHeader($subjectPosition, 19, 6, 5)) // '100px', '-90px'))
            ->addSliceArray($this->setGradeLayoutBody($subjectPosition, $this->getTypeId(), 19, 14, false, 6, 5, '15px'))

            // Sek II
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->styleHeight('40px')
                )
            )
            ->addSlice($this->setLetterRow())
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderRight($thicknessOutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Gymnasium')
                        ->styleHeight('30px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                        ->stylePaddingTop('7px')
                        ->stylePaddingLeft('5px')
                        ->styleBorderRight($thicknessInnerLines)
                        , '18%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Name')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('Vorname')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('
                                    {% if( Content.Person.Data.Name.Last is not empty) %}
                                        {{ Content.Person.Data.Name.Last }}
                                    {% else %}
                                        &nbsp;
                                    {% endif %}'
                                )
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('4px')
                                ->styleTextSize($InputText)
                                ->styleHeight('24.5px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                    {% if( Content.Person.Data.Name.First is not empty) %}
                                        {{ Content.Person.Data.Name.First }}
                                    {% else %}
                                        &nbsp;
                                    {% endif %}'
                                )
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('4px')
                                ->styleTextSize($InputText)
                                ->styleHeight('24.5px')
                                , '40%')
                        )
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderTop($thicknessInnerLines)
                ->styleBorderRight($thicknessOutLines)
                ->styleBorderBottom($thicknessOutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Sekundarstufe II')
                        ->styleTextSize('12px')
                        ->styleHeight('16.5px')
                        ->stylePaddingTop('4px')
                        ->stylePaddingBottom('4px')
                        ->stylePaddingLeft('4px')
                        ->styleTextBold()
                    )
                )
            )
            ->addSliceArray($this->setGradeLayoutHeaderForSekII($subjectPositionSekII))
            ->addSliceArray($this->setGradeLayoutBodyForSekII($subjectPositionSekII, $this->getTypeId()))
            ;
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0')
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
            ->addPage($this->buildRemarkPage($this->getType() ? $this->getType() : null))
        );
    }

    /**
     * @param array $subjectPosition
     * @param int $countSubjectColumns
     * @param int $widthFirstColumns
     * @param int $widthLastColumns
     * @param string $heightHeader
     * @param string $paddingLeftHeader
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $textSizeSmall
     *
     * @return array
     */
    protected function setGradeLayoutHeaderForSekII(
        &$subjectPosition = array(),
        $countSubjectColumns = 23,
        $widthFirstColumns = 6,
        $widthLastColumns = 5,
        $heightHeader = '170px',
        $paddingLeftHeader = '-90px',
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $textSizeSmall = '9px'
    ) {

        $countGradesTotal = $countSubjectColumns;
        $widthFirstColumnsString = $widthFirstColumns . '%';
        $widthLastColumnsString = $widthLastColumns . '%';
        $width = (100 - 3 * $widthFirstColumns - 2 * $widthLastColumns) / $countGradesTotal;
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
        $section
            ->addElementColumn((new Element())
                ->setContent('Leistungen in den einzelnen Fächern (Punkte und Kursart)')
                ->styleAlignCenter()
                ->styleTextSize('12px')
                ->styleHeight('16.6px')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderBottom($thicknessInnerLines)
                , ($countSubjectColumns * $width). '%'
            );
        for ($i = 1; $i <= 2; $i++) {
            $element = (new Element())
                ->setContent('&nbsp;')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines)
                ->styleBorderRight($i == 2 ? $thicknessOutLines : '0px');

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
                case 1: $text = 'Jahrgangsstufe'; break;
                case 2: $text = 'Schuljahr'; break;
                case 3: $text = 'Kurshalbjahr'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text, '-45px', $paddingLeftHeader))
                ->styleHeight($heightHeader)
                ->styleTextSize($textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthFirstColumnsString);
        }
        if ($this->getTblPerson()) {
            $tblSubjectList = Generator::useService()->getStudentCardSubjectListForSekIIByPerson($this->getTblPerson());
        } else {
            $tblSubjectList = false;
        }

        for ($i = 0; $i < $countSubjectColumns; $i++) {
            $text = '&nbsp;';
            if ($tblSubjectList && isset($tblSubjectList[$i])) {
                $tblSubject = $tblSubjectList[$i];
                if ($tblSubject) {
                    $text = $tblSubject->getName();
                    if ($text == 'Gemeinschaftskunde/Rechtserziehung/Wirtschaft') {
                        $text = 'Gemeinschaftskunde / Rechtserziehung / Wirtschaft';
                    }
                    $subjectPosition[$i] = $tblSubject;

                    if ($i == 0) {
                        $text .= ' (1.LF)';
                    }
                    if ($i == 1) {
                        $text .= ' (2.LF)';
                    }
                }
            }

            $element = (new Element())
                ->setContent($this->setRotatedContend($text, '-45px', $paddingLeftHeader))
                ->styleHeight($heightHeader)
                ->styleTextSize(strlen($text) > 35 ? '7px' : $textSizeSmall)
                ->styleBorderLeft($i == 0 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthString);
        }
        for ($i = 1; $i <= 2; $i++) {
            $text = '&nbsp;';
            switch ($i) {
                case 1: $text = 'Datum des Zeugnisses'; break;
                case 2: $text = 'Signum des Lehrers'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text, '-45px', $paddingLeftHeader))
                ->styleHeight($heightHeader)
                ->styleTextSize($textSizeSmall)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines)
                ->styleBorderRight($i == 2 ? $thicknessOutLines : '0px');

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
     * @param int $widthFirstColumns
     * @param int $widthLastColumns
     * @param string $heightRow
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $textSizeSmall
     * @param string $textSizeNormal
     * @return array
     */
    protected function setGradeLayoutBodyForSekII(
        $subjectPosition = array(),
        $typeId = 0,
        $countSubjectColumns = 23,
        $countRows = 8,
        $widthFirstColumns = 6,
        $widthLastColumns = 5,
        $heightRow = '18px',
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $textSizeSmall = '8px',
        $textSizeNormal = '11px'
    ) {

        $countGradesTotal = $countSubjectColumns;
        $widthFirstColumnsString = $widthFirstColumns . '%';
        $widthLastColumnsString = $widthLastColumns . '%';
        $width = (100 - 3 * $widthFirstColumns - 2 * $widthLastColumns) / $countGradesTotal;
        $widthString = $width . '%';
        $countTotalColumns = 3 + $countGradesTotal + 2;
        $offset = 100;

        $sliceList = array();
        for ($j = $offset + 1; $j <= $offset + $countRows; $j++) {
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
                    $content = '{% if(Content.Certificate.' . $typeId . '.Data' . $j . '.MidTerm is not empty) %}
                                {{ Content.Certificate.' . $typeId . '.Data' . $j . '.MidTerm }}
                            {% else %}
                                 &nbsp;
                            {% endif %}';
                }  elseif ($i >= 4 && $i <= (3 + $countGradesTotal)) {
                    $thicknessLeft = $i == 4 ? $thicknessOutLines : $thicknessInnerLines;
                    $widthColumn = $widthString;

                    if (isset($subjectPosition[$i - 4])) {
                        $tblSubject = $subjectPosition[$i - 4];
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
                    $paddingTop = '7px';
                    $height = '15px';
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
                    ->styleBorderBottom($j == $countRows + $offset ? $thicknessOutLines: '0px');
                $section->addElementColumn($element, $widthColumn);
            }
            $slice->addSection($section);
            $sliceList[] = $slice;
        }

        return $sliceList;
    }
}