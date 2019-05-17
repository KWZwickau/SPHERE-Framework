<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.05.2019
 * Time: 08:43
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CmsMsHjBeiblatt
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CMS
 */
class CmsMsHjBeiblatt extends CmsStyle
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

        return $this->getPage($personId, 'Beiblatt zur Halbjahresinformation', false);
    }

    /**
     * @param $personId
     * @param $titleText
     * @param $isSignatureExtended
     *
     * @return Page
     */
    public function getPage($personId, $titleText, $isSignatureExtended)
    {
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
                    self::getCMSHeadLine($titleText)
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
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                                {{ Content.P'.$personId.'.Input.RemarkWithoutTeam|nl2br }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleAlignJustify()
                        ->styleMarginTop('15px')
                        ->styleHeight('585px')
                    )
                )
                ->addSection(
                    self::getCMSDate($personId)
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        self::getCMSTeacher($personId, $isSignatureExtended)
                    )
                )
                ->addElement((new Element())
                    ->styleMarginTop('20px')
                )
                ->addSectionList(
                    self::getCMSCustody()
                )
            );
    }
}