<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswGsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswGsJOne extends Certificate
{
    /**
     * @return array
     */
    public function selectValuesTransfer()
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt",
            3 => "kein Versetzungsvermerk"
        );
    }

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page[]
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        // Klasse 1 hat keinen Versetzungsvermerk
        if ($this->getLevel() == 1) {
            $hasTransfer = false;
        } else {
            $hasTransfer = true;
        }

        $page = (new Page())
            ->addSlice(CswGsStyle::getHeader($this->isSample()))
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
            ->addSlice($this->getDivisionAndYear($personId))
            ->addSlice($this->getStudentName($personId))
            ->addSlice($this->getDescriptionContent($personId, $hasTransfer ? '506px' : '530px', '20px'));

        if ($hasTransfer) {
            $page->addSlice($this->getTransferCustom($personId));
        }

        $page
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
        $pageList[] = $page;

        $pageList[] = CswGsStyle::buildSecondPage($tblPerson);

        return $pageList;
    }

    public function getTransferCustom($personId, $MarginTop = '5px')
    {
        $TransferSlice = (new Slice());
        $TransferSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent(
                    '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        Versetzungsvermerk:
                    {% endif %}')
                , '22%')
            ->addElementColumn((new Element())
                ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                        {{ Content.P' . $personId . '.Input.Transfer }}.
                    {% else %}
                          &nbsp;
                    {% endif %}')
                ->styleBorderBottom(
                    '{% if(Content.P' . $personId . '.Input.Transfer) %}
                        1px
                    {% else %}
                        0px
                    {% endif %}'
                )
                , '58%')
            ->addElementColumn((new Element())
                , '20%')
        )
            ->styleMarginTop($MarginTop);
        return $TransferSlice;
    }
}