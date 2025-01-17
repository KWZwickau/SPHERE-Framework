<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CmsGsHjOneTwoExt
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
class CmsGsHjOneTwoExt extends CmsStyle
{


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
        $PageList = array();
        $PageList[] = (new Page())
            ->addSlice((new Slice())
                ->stylePaddingLeft('16px')
                ->stylePaddingRight('16px')
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCMSHead()
                    )
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSectionList(
                    self::getCMSSchoolLine($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSection(
                    self::getCMSHeadLine('Halbjahresinformation der Grundschule')
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSection(
                    self::getCMSDivisionAndYear($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSection(
                    self::getCMSName($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSectionList(
                    self::getCMSRemark($personId, '600px')
                )
            );
        $PageList[] = (new Page())
            ->addSlice((new Slice())
                ->stylePaddingLeft('16px')
                ->stylePaddingRight('16px')
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSection(
                    self::getCMSExtendedName($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSectionList(
                    self::getCMSSecondRemark($personId, '739px')
                )
                ->addSection(
                    self::getCMSMissing($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('15px')
                )
                ->addSection(
                    self::getCMSDate($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCMSTeacher($personId, true)
                    )
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSectionList(
                    self::getCMSCustody()
                )
                ->addSectionList(
                    self::getCMSFoot()
                )
            );
        return $PageList;
    }
}