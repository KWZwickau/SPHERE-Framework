<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 09:13
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class AbstractStudentCard
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
abstract class AbstractStudentCard extends AbstractDocument
{

    /**
     * @param int $widthFirstColumns
     * @param int $widthLastColumns
     * @param string $heightHeader
     * @param int $countSubjectColumns
     * @param string $thicknessOutLines
     * @param string $thicknessInnerLines
     * @param string $smallTextSize
     *
     * @return array
     */
    protected function setGradeLayoutHeader(
        $widthFirstColumns = 5,
        $widthLastColumns = 4,
        $heightHeader = '100px',
        $countSubjectColumns = 18,
        $thicknessOutLines = '1.2px',
        $thicknessInnerLines = '0.5px',
        $smallTextSize = '7px'
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
//                ->setContent($width)
                ->styleAlignCenter()
                ->styleTextSize('12px')
                ->styleHeight('16.5px')
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderBottom('0.5px')
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
                ->setContent($this->setRotatedContend($text, '6px'))
                ->styleHeight($heightHeader)
                ->styleTextSize($smallTextSize)
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
                ->styleTextSize($smallTextSize)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines);

            $section->addElementColumn($element, $widthString);
        }
        for ($i = 1; $i <= $countSubjectColumns; $i++) {
            $text = '&nbsp;';
            switch ($i) {
                // ToDo dynamic
//                case 1: $text = 'Betragen'; break;
//                case 2: $text = 'Fleiß'; break;
                case 3: $text = 'Deutsch'; break;
                case 4: $text = 'Sachunterricht'; break;
            }
            $element = (new Element())
                ->setContent($this->setRotatedContend($text))
                ->styleHeight($heightHeader)
                ->styleTextSize($smallTextSize)
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
                ->styleTextSize($smallTextSize)
                ->styleBorderLeft($i == 1 ? $thicknessOutLines : $thicknessInnerLines)
                ->styleBorderRight($i == 4 ? $thicknessOutLines : '0px');

            $section->addElementColumn($element, $widthLastColumnsString);
        }
        $slice->addSection($section);
        $sliceList[] = $slice;

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
}