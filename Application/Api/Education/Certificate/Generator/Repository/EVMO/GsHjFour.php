<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsHjFour
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO
 */
class GsHjFour extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->stylePaddingLeft('45px')
                ->stylePaddingRight('45px')
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomHead($personId, 'Halbjahresinformation', '1. Schulhalbjahr')
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomStudentNameForGrades($personId, '20px')
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getGradeLanesSmall($personId, '11pt', true, '10px', '#DDD')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Leistungen in den einzelnen Fächern:')
                        ->styleTextSize('11pt')
                        ->styleTextBold()
                        ->styleMarginTop('14px')
                        ->styleMarginBottom('8px')
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        (new Slice())
                            ->styleHeight('140px')
                            ->addSectionList(self::getSubjectLanesSmall($personId, false, array(), '11pt', true, false, false, '#DDD'))
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Notenerläuterungen: 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                                    ->styleMarginTop('14px')
                                    ->styleTextSize('5pt')
                                )
                            )

                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getDescriptionContent($personId, '340px', '35px', '', '11pt')
                    )
                )
                ->addSection(self::getCustomMissing($personId))
                ->addSection(self::getCustomDate($personId))
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomSignPart($personId, false, '25px')
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomParentSign()
                    )
                )
            );
    }
}