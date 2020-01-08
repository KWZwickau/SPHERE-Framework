<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CswGsStyle
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\CSW
 */
class CswGsStyle
{
    public static function buildSecondPage(TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/CSW_GS.jpg',
                    '', '165px'))
                    ->styleAlignCenter()
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('
                        Grumbach, 
                        {% if(Content.P'.$personId.'.Input.Date is not empty) %}
                            {{ Content.P'.$personId.'.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleAlignRight()
                    ->styleMarginTop('50px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.StudentLetter is not empty) %}
                            {{ Content.P' . $personId . '.Input.StudentLetter|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleMarginTop('20px')
                )
            );
    }
}