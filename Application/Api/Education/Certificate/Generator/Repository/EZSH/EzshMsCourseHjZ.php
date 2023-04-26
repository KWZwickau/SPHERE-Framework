<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EzshMsCourseHjZ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH
 */
class EzshMsCourseHjZ extends EzshStyle
{
    /**
     * @param $personId
     *
     * @return Page
     */
    private function firstPage($personId)
    {
        $Page = (new Page())
            ->addSlice(
                (new Slice())
                    ->stylePaddingLeft('50px')
                    ->stylePaddingRight('50px')
                    ->addSection((new Section())
                        ->addSliceColumn(
                            self::getEZSHSample()
                        )
                    )
                    ->addSectionList(
                        self::getEZSHHeadLine('HALBJAHRESZEUGNIS', 'OBERSCHULE – staatlich anerkannte Ersatzschule')
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('35px')
                    )
                    ->addSection(
                        self::getEZSHName($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('35px')
                    )
                    ->addSection(
                        self::getEZSHDivisionAndYear($personId)
                    )
                    ->addSection(self::getEZSHCourse($personId))
                    ->addSection((new Section())
                        ->addSliceColumn(
                            self::getEZSHSubjectLanes($personId, true, array(), false)
                        )
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('10px')
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            self::getEZSHObligation($personId)
                        )
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('15px')
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('35px')
                    )
                    ->addSectionList(
                        self::getEZSHGradeInfo(false)
                    )
                    ->addSectionList(
                        self::getEZSHArrangement($personId, '120px')
                    )
                    ->addSectionList(
                        self::getEZSHMissing($personId)
                    )
            );
        return $Page;
    }

    /**
     * @param $personId
     *
     * @return Page
     */
    private function secondPage($personId)
    {
        $Page = (new Page())
            ->addSlice(
                (new Slice())
                    ->stylePaddingLeft('50px')
                    ->stylePaddingRight('50px')
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop('75px')
                    )
                    ->addSectionList(
                        self::getEZSHRemark($personId, '720px')
                    )
                    ->addSectionList(
                        self::getEZSHDateSign($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('63px')
                    )
                    ->addSectionList(
                        self::getEZSHCustody()
                    )
            );
        return $Page;
    }


    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return array(
            self::firstPage($personId),
            self::secondPage($personId),
            self::getRatingPage($personId, 'Anlage zum HALBJAHRESZEUGNIS', 'OBERSCHULE – staatlich anerkannte Ersatzschule')
        );
    }
}