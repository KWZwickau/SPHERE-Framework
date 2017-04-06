<?php

namespace SPHERE\Application\Api\Document\Custom\Radebeul\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Custom\Radebeul\Radebeul;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Person;

class StudentList extends AbstractDocument
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerliste / Lerngruppe';
    }

    /**
     * @return Slice[]
     */
    public function buildContent()
    {
        $textSize = '11px';
        $fontFamily = 'MetaPro';
        $columnHeight = '27';
        $lineHeight = '65%';//'65%';

//        $slice = new Slice();
        $elementList = array();
        $maxRowCountList = array();
        $groupList = array();
        $tblPersonList = Radebeul::getPersonListByRadebeul();
        if ($tblPersonList) {
            foreach ($tblPersonList as $levelName => $groupList) {
                foreach ($groupList as $groupName => $personList) {
                    $rowCount = 0;
                    $groupList[$groupName] = $groupName;
                    // reorder Array (required vor building structure) and fill it with elements
                    foreach ($personList as $personId => $value) {
                        $rowCount++;
                        if (($tblPerson = Person::useService()->getPersonById($personId))) {
                            $firstName = $tblPerson->getFirstName();
                            $secondName = $tblPerson->getSecondName();
                            $elementFirstName = (new Element())
                                ->setContent($firstName.($secondName ? '<br>'.$secondName : ''))
                                ->styleBorderBottom()
                                ->styleBorderRight()
                                ->styleBorderTop()
                                ->styleHeight((strlen($firstName) > 15 && preg_match('![\s]!', $firstName)
                                    ? $columnHeight.'px'
                                    : ($secondName
                                        ? $columnHeight.'px'
                                        : ($columnHeight - 8).'px')))
                                ->styleFontFamily($fontFamily)
                                ->styleTextSize($textSize)
                                ->styleLineHeight($lineHeight)
                                ->styleAlignCenter()
                                ->stylePaddingTop((strlen($firstName) > 15 && preg_match('![\s]!', $firstName)
                                    ? '0px'
                                    : ($secondName
                                        ? '0px'
                                        : '8px')))
                                ->styleBackgroundColor($rowCount % 2 == 1 ? '#E4E4E4' : '#FFF');
                            $elementLastName = (new Element())
                                ->setContent($tblPerson->getLastName())
                                ->styleBorderAll()
                                ->styleHeight(($columnHeight - 8).'px')
                                ->styleFontFamily($fontFamily)
                                ->styleTextSize($textSize)
                                ->styleLineHeight($lineHeight)
                                ->styleAlignCenter()
                                ->stylePaddingTop('8px')
                                ->styleBackgroundColor($rowCount % 2 == 1 ? '#E4E4E4' : '#FFF');

                            $elementList[$levelName][$rowCount][$groupName][1] = $elementLastName;
                            $elementList[$levelName][$rowCount][$groupName][2] = $elementFirstName;
                        }

                        if (isset($maxRowCountList[$levelName])) {
                            if ($rowCount > $maxRowCountList[$levelName]) {
                                $maxRowCountList[$levelName] = $rowCount;
                            }
                        } else {
                            $maxRowCountList[$levelName] = $rowCount;
                        }
                    }
                }
            }
        }

        // max count for each level (fill missing items with empty elements)
        foreach ($maxRowCountList as $levelName => $MaxRowCount) {
            foreach ($groupList as $group) {
                for ($i = 1; $i <= $MaxRowCount; $i++) {
                    if (!isset($elementList[$levelName][$i][$group][1])) {
                        $elementList[$levelName][$i][$group][1] = (new Element())->setContent('&nbsp;');
                    }
                    if (!isset($elementList[$levelName][$i][$group][2])) {
                        $elementList[$levelName][$i][$group][2] = (new Element())->setContent('&nbsp;');
                    }
                }
            }
        }

        $sliceArray = array();

        foreach ($elementList as $levelName => $rows) {
            $slice = new Slice();
            foreach ($rows as $rowCount => $groups) {
                // space between Names
                $slice->addElement((new Element())->setContent('&nbsp;')
                    ->styleTextSize('2px'));

                $section = new Section();
                $slice->addSection($section);
                //first spring
                if (isset($groups['Frühling'])) {
                    foreach ($groups['Frühling'] as $item) {
                        $section->addElementColumn(
                            $item, '12%'
                        );
                    }
                    $section->addElementColumn((new Element())->setContent('&nbsp;'), '1%');
                }
                //second summer
                if (isset($groups['Sommer'])) {
                    foreach ($groups['Sommer'] as $item) {
                        $section->addElementColumn(
                            $item, '12%'
                        );
                    }
                    $section->addElementColumn((new Element())->setContent('&nbsp;'), '1%');
                }
                //third autumn
                if (isset($groups['Herbst'])) {
                    foreach ($groups['Herbst'] as $item) {
                        $section->addElementColumn(
                            $item, '12%'
                        );
                    }
                    $section->addElementColumn((new Element())->setContent('&nbsp;'), '1%');
                }
                //fourth winter
                if (isset($groups['Winter'])) {
                    foreach ($groups['Winter'] as $item) {
                        $section->addElementColumn(
                            $item, '12%'
                        );
                    }
                    $section->addElementColumn((new Element())->setContent('&nbsp;'), '1%');
                }
            }

            // space between levels
            $slice->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('8px'));

            $sliceArray[] = $slice;
        }

        return $sliceArray;
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        $fontFamily = 'MetaPro';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Frühling')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('16px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Sommer')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('16px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Herbst')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('16px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Winter')
                            ->styleFontFamily($fontFamily)
                            ->styleTextSize('16px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            , '25%'
                        )
                    )
                )
                ->addSliceArray($this->buildContent())
            )
        );
    }
}