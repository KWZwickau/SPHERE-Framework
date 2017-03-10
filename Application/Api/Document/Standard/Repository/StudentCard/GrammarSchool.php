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

/**
 * Class GrammarSchool
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentCard
 */
class GrammarSchool  extends AbstractStudentCard
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerkartei - Gymnasium';
    }

    /**
     * @return Frame
     */
    public function buildDocument()
    {

        $SmallTextSize = '7px';
        $InputText = '12px';
        $OutLines = '1.2px';

        $subjectPosition = array();

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice($this->setLetterRow())
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('&nbsp;')
                        ->styleHeight('20px')
                    )
                )
                ->addSlice(( new Slice() )
                    ->addSection(( new Section() )
                        ->addSliceColumn(( new Slice() )
                            ->addSectionList(array(
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('Familienname/Vorname')
                                        ->stylePaddingLeft('4px')
                                        ->styleTextSize($SmallTextSize)
                                        ->styleBorderTop($OutLines)
                                        ->styleBorderLeft($OutLines)
                                        ->styleBorderRight($OutLines)
                                    ),
                                ( new Section() )
                                    ->addElementColumn(( new Element() )
                                        ->setContent('
                                            {% if( Content.Person.Data.Name.First is not empty) %}
                                                {{ Content.Person.Data.Name.Last }} {{ Content.Person.Data.Name.First }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                                        ->stylePaddingLeft('4px')
                                        ->stylePaddingTop('1px')
                                        ->styleTextSize($InputText)
                                        ->styleBorderBottom($OutLines)
                                        ->styleBorderLeft($OutLines)
                                        ->styleBorderRight($OutLines)
                                        ->styleHeight('18px')
                                    )
                            ))
                        )
                    )
                )
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('Gymnasium')
                        ->styleMarginTop('10px')
                        ->styleTextSize('18px')
                        ->styleTextBold()
                    )
                )
                ->addSlice(( new Slice() )
                    ->addElement(( new Element() )
                        ->setContent('Sekundarstufe I / besuchtes Profil: '
                        . '{% if(Content.Student.Profile is not empty) %}
                                {{ Content.Student.Profile }}
                            {% else %}
                                &nbsp;
                            {% endif %}')
                        ->styleMarginTop('10px')
                        ->styleTextSize('12px')
                        ->styleHeight('16.5px')
                        ->stylePaddingLeft('4px')
                        ->styleBorderLeft($OutLines)
                        ->styleBorderTop($OutLines)
                        ->styleBorderRight($OutLines)
                    )
                )
                ->addSliceArray($this->setGradeLayoutHeader($subjectPosition, 19))
                ->addSliceArray($this->setGradeLayoutBody($subjectPosition, 19, 15))
            )
        );
    }
}