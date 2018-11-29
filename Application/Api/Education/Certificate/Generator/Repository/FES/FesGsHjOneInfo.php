<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.11.2018
 * Time: 16:12
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FES;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class FesGsHjOneInfo
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\FES
 */
class FesGsHjOneInfo extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Halbjahresinformation der Grundschule'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getDescriptionContent($personId, '600px', '20px'))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage entschuldigt:')
                        , '22%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage unentschuldigt:')
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                    )
                )
                ->styleMarginTop('15px')
            )
            ->addSlice($this->getDateLine($personId))
            ->addSlice($this->getSignPart($personId, false))
            ->addSlice($this->getParentSign()
            );
    }
}
