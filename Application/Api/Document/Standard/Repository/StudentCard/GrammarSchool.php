<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 10.03.2017
 * Time: 10:36
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Type;

/**
 * Class GrammarSchool
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentCard
 */
class GrammarSchool extends AbstractStudentCard
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerkartei - Gymnasium';
    }

    /**
     * @return int
     */
    public function getTypeId()
    {

        if (($tblType = Type::useService()->getTypeByName('Gymnasium'))) {
            return $tblType->getId();
        } else {
            return 0;
        }
    }

    /**
     * @return Page
     */
    public function buildPage()
    {

        $SmallTextSize = '7px';
        $InputText = '12px';
        $thicknessOutLines = '1.2px';
        $thicknessInnerLines = '0.5px';

        $subjectPosition = array();

        return (new Page())
            ->addSlice($this->setLetterRow())
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderTop($thicknessOutLines)
                ->styleBorderRight($thicknessOutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Gymnasium')
                        ->styleHeight('30px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                        ->stylePaddingTop('7px')
                        ->stylePaddingLeft('5px')
                        ->styleBorderRight($thicknessInnerLines)
                        , '18%')
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Name')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('Vorname')
                                ->stylePaddingLeft('4px')
                                ->styleTextSize($SmallTextSize)
                                , '40%')
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('
                                                {% if( Content.Person.Data.Name.Last is not empty) %}
                                                    {{ Content.Person.Data.Name.Last }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('4px')
                                ->styleTextSize($InputText)
                                ->styleHeight('24.5px')
                                , '40%')
                            ->addElementColumn((new Element())
                                ->setContent('
                                                {% if( Content.Person.Data.Name.First is not empty) %}
                                                    {{ Content.Person.Data.Name.First }}
                                                {% else %}
                                                    &nbsp;
                                                {% endif %}')
                                ->stylePaddingLeft('4px')
                                ->stylePaddingTop('4px')
                                ->styleTextSize($InputText)
                                ->styleHeight('24.5px')
                                , '40%')
                        )
                    )
                )
            )
            ->addSlice((new Slice())
                ->styleBorderLeft($thicknessOutLines)
                ->styleBorderTop($thicknessInnerLines)
                ->styleBorderRight($thicknessOutLines)
                ->styleBorderBottom($thicknessOutLines)
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Sekundarstufe I')
                        ->styleTextSize('12px')
                        ->styleHeight('16.5px')
                        ->stylePaddingTop('4px')
                        ->stylePaddingBottom('4px')
                        ->stylePaddingLeft('4px')
                        ->styleTextBold()
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent(
                            'Besuchtes Profil:'
                            . '{% if(Content.Student.Profile is not empty) %}
                                    {{ Content.Student.Profile }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                        )
                        ->styleTextSize('12px')
                        ->styleHeight('16.5px')
                        ->stylePaddingTop('4px')
                        ->stylePaddingBottom('4px')
                        ->stylePaddingLeft('4px')
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->styleHeight('8px')
                )
            )
            ->addSliceArray($this->setGradeLayoutHeader($subjectPosition, 19))
            ->addSliceArray($this->setGradeLayoutBody($subjectPosition, $this->getTypeId(), 19, 16));

            // Todo Sek II
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage()
            )
        );
    }
}