<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\HOGA;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class MsAbsRs extends Style
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        // leere Seite
        $pageList[] = new Page();

        $pageList[] = $this->getCoverPage('ABSCHLUSSZEUGNIS', 'der Oberschule', '');

        $paddingTop = '4px';
        $marginSpace = '45px';
        $page = $this->getSecondPageTop($personId, $marginSpace);
        $page
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('hat die')
                        ->stylePaddingTop($paddingTop))
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('170px')
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->getElement('Oberschule', self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleTextBold()
                            ->styleMarginTop('-6px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->getElement('der HOGA Schloss Albrechtsberg', self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleMarginTop('-6px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->getElement('gemeinnützige Schulgesellschaft mbH', self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleMarginTop('-6px')
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn(
                        $this->getElement('staatlich anerkannte Schule in freier Trägerschaft', self::TEXT_SIZE_SMALL)
                            ->styleAlignCenter()
                            ->styleMarginTop('-6px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('30px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement('besucht')
                        ->stylePaddingTop($paddingTop))
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop('80px')
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            'und hat nach Bestehen der Abschlussprüfung in der Klassenstufe 10 den',
                            self::TEXT_SIZE_SMALL
                        )
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            'REALSCHULABSCHLUSS',
                            '24px'
                        )
                        ->styleAlignCenter()
                        ->styleTextBold()
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleMarginTop($marginSpace)
                ->addSection((new Section())
                    ->addElementColumn($this->getElement(
                            'erworben.',
                            self::TEXT_SIZE_SMALL
                        )
                    )
                )
            );

        $pageList[] = $page;

        $pageList[] = (new Page())
            ->addSlice($this->getStudentHeader($personId, true))
            ->addSlice($this->getSliceSpace('15px'))
            ->addSlice($this->getCustomSubjectLanes($personId, true, array(), false, true)->styleHeight('320px'))
            ////
            ->addSlice($this->getCustomAdditionalSubjectLanes($personId))
            ////
            ->addSlice($this->getCustomRemark($personId, '5px', '160px'))
            ->addSlice($this->getCustomDateLine($personId))
            ->addSlice($this->getCustomExaminationsBoard())
            ->addSlice($this->getCustomInfo('40px'));

        return $pageList;
    }
}