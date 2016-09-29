<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 15:54
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC\GradeInformation;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;

/**
 * Class ChemnitzGradeInformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC\GradeInformation
 */
class ChemnitzGradeInformation extends Certificate
{

    /**
     * @param bool $IsSample
     *
     * @return Frame
     */
    public function buildCertificate($IsSample = true)
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Noteninformation für ')
                            ->styleTextSize('15px')
                            ->styleTextBold()
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.Person.Data.Name is not empty) %}
                                    {{ Content.Person.Data.Name.First }}
                                    {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                            ->styleTextSize('13px')
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '44%')
                        ->addElementColumn((new Element())
                            ->setContent(', Klasse {{ Content.Division.Data.Level.Name }}{{ Content.Division.Data.Name }} ,')
                            ->styleTextSize('15px')
                            ->stylePaddingLeft('4px')
                            ->styleTextBold()
                            , '22%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.Input.Date is not empty) %}
                                    {{ Content.Input.Date }}
                                {% else %}
                                    &nbsp;
                                {% endif %}
                            ')
                            ->styleTextSize('15px')
                            ->styleTextBold()
                            , '12%')
                    )
                )
            )
        );
    }
}