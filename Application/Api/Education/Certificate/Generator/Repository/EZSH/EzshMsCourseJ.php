<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.01.2019
 * Time: 08:34
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EzshMsCourseJ
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EZSH
 */
class EzshMsCourseJ extends EzshStyle
{
    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }

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
                        self::getEZSHHeadLine('JAHRESZEUGNIS', 'OBERSCHULE – staatlich genehmigte Ersatzschule')
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
                            self::getEZSHSubjectLanes($personId)
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
                    ->addSectionList(
                        self::getEZSHPerformanceGroup($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('35px')
                    )
                    ->addSectionList(
                        self::getEZSHGradeInfo()
                    )
                    ->addSectionList(
                        self::getEZSHArrangement($personId, '90px')
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
                        self::getEZSHRating($personId, '510px')
                    )
                    ->addSectionList(
                        self::getEZSHTransfer($personId)
                    )
                    ->addSectionList(
                        self::getEZSHRemark($personId)
                    )
                    ->addSectionList(
                        self::getEZSHDateSign($personId)
                    )
                    ->addElement((new Element())
                        ->styleMarginTop('55px')
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
            self::secondPage($personId)
        );
    }
}