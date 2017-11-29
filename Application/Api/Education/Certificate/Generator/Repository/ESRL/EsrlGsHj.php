<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESRL;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class EsrlGsHj
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESRL
 */
class EsrlGsHj extends EsrlStyle
{

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->styleBorderAll('3px', '#050')
                ->stylePaddingTop('20px')
                ->stylePaddingLeft('20px')
                ->stylePaddingRight('20px')
                ->stylePaddingBottom('20px')
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getESRLHead()
                    )
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection(
                    self::getESRLHeadLine('HALBJAHRESINFORMATION')
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection(
                    self::getESRLDivisionAndYear($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection(
                    self::getESRLName($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('15px')
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getESRLHeadGrade($personId)
                    )
                )
                ->addElement((new Element())
                    ->styleMarginTop('15px')
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        (self::getESRLSubjectLanes($personId, '175px'))
                    )
                )
                ->addSection(
                    self::getESRLRemark($personId, '242px')
                )
                ->addSection(
                    self::getESRLMissing($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('15px')
                )
                ->addSection(
                    self::getESRLDate($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('10px')
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getESRLTeacher($personId, false)
                    )
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSectionList(
                    self::getESRLCustody()
                )
                ->addSectionList(
                    self::getESRLFooter()
                )
            );
    }
}