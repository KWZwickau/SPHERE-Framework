<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsJOneTwo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO
 */
class GsJOneTwo extends Style
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
                ->stylePaddingLeft('45px')
                ->stylePaddingRight('45px')
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomHead($personId, 'Jahreszeugnis', 'Schuljahr')
                    )

                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomStudentName($personId, '35px')
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getDescriptionContent($personId, '530px', '35px', '', '11pt')
                    )
                )
                ->addSection(self::getCustomMissing($personId))
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomTransfer($personId)
                    )
                )
                ->addSection(self::getCustomDate($personId))
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCustomSignPart($personId, true, '25px')
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