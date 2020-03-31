<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\LWSZ;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class LwszGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\LWSZ
 */
class LwszGsJOne extends Certificate
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $pageList[] = (new Page())
            ->addSlice(LwszGsStyle::getHeader($this->isSample()))
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getDescriptionContent($personId, '530px', '20px'))
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
            ->addSlice($this->getSignPart($personId, true))
            ->addSlice($this->getParentSign()
            );

        $pageList[] = LwszGsStyle::buildSecondPage($tblPerson);

        return $pageList;
    }
}