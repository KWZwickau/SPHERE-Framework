<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CmsGsJOneTwo
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
class CmsGsJOneTwo extends CmsStyle
{

    /**
     * @return array
     */
    public function selectValuesTransfer(): array
    {
        if ($this->getLevel() == 1) {
            // Versetzungevermerk wird für Klasse 1 deaktiviert
            return array(1 => "Nicht verfügbar");
        }
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

        // Versetzungsvermerk wird für Klasse 1 nicht mehr angezeigt
        $Transfer = self::getCMSTransfer($personId);
        if($this->getLevel() == 1){
            $SectionTransfer = new Section();
            $Transfer = $SectionTransfer->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleMarginTop('1px')
            );
        }

        return (new Page())
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
                    self::getCMSHeadLine('Jahreszeugnis der Grundschule')
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
                    self::getCMSRemark($personId, '477px')
                )
                ->addSection(
                    self::getCMSMissing($personId)
                )
                ->addElement((new Element())
                    ->styleMarginTop('15px')
                )
                ->addSection(
                    $Transfer
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
    }
}