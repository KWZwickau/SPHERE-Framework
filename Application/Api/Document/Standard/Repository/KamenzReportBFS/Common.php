<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class Common
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\KamenzReportBFS
 */
class Common
{
    /**
     * @param $count
     *
     * @return string
     */
    public static function getBlankSpace($count)
    {
        $result = '';
        for ($i = 0; $i < $count; $i++) {
            $result .= '&nbsp;';
        }

        return $result;
    }

    /**
     * @param Section $section
     * @param string $content
     * @param string $width
     * @param false $isAlignCenter
     * @param bool $isLastColumn
     */
    public static function setContentElement(Section &$section, $content, $width, $isAlignCenter = false, $isLastColumn = false)
    {
        $element = (new Element())
            ->setContent('
                    {% if (' . $content . ' is not empty) %}
                        {{ ' . $content . ' }}
                    {% else %}
                        &nbsp;
                    {% endif %}
                ');

        if ($isAlignCenter) {
            $element->styleAlignCenter();
        } else {
            $element->stylePaddingLeft('5px');
        }

        if (!$isLastColumn) {
            $element->styleBorderRight();
        }

        $section->addElementColumn($element, $width);
    }

    /**
     * @param string $name
     * @param string $identifier
     * @param bool $isLastColumn
     *
     * @return Slice
     */
    public static function setTotalSlice($name, $identifier, $isLastColumn = false)
    {
        $preText = 'Content.' . $name . '.TotalCount.' . $identifier . '.';
        $slice = new Slice();
        self::setTotalElement($slice, $preText . 'm', $isLastColumn);
        self::setTotalElement($slice, $preText . 'w', $isLastColumn);
        self::setTotalElement($slice, $preText . 'd', $isLastColumn);
        self::setTotalElement($slice, $preText . 'o', $isLastColumn);
        self::setTotalElement($slice, $preText . 'TotalCount', $isLastColumn, true);

        return $slice;
    }

    /**
     * @param Slice $slice
     * @param string $content
     * @param bool $isLastColumn
     * @param bool $isLastRow
     */
    private static function setTotalElement(Slice &$slice, $content, $isLastColumn = false, $isLastRow = false)
    {
        $element = (new Element())
            ->setContent('
                {% if (' . $content . ' is not empty) %}
                    {{ ' . $content . ' }}
                {% else %}
                    &nbsp;
                {% endif %}
            ')
            ->styleAlignCenter();

        if (!$isLastColumn) {
            $element->styleBorderRight();
        }

        if (!$isLastRow) {
            $element->styleBorderBottom();
        }

        $slice->addElement($element);
    }

    /**
     * @param $array
     *
     * @return Slice
     */
    public static function setFootnotes($array)
    {
        $slice = (new Slice())->styleMarginTop('7px');

        $i = 1;
        foreach ($array as $row) {
            $slice->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent($i++ . ')' . self::getBlankSpace(2) . $row)
                    ->styleTextSize('13px')
                    ->styleMarginTop('8px')
                )
            );
        }

        return $slice;
    }
}