<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsHjOneTwo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVMO
 */
class GsHjOneTwo extends Style
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
                        self::getCustomStudentName($personId, '130px')
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getDescriptionContent($personId, '460px', '35px', '', '11pt')
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