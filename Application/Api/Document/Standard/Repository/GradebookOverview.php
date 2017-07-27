<?php
/**
 * Created by PhpStorm.
 * User: lehmann
 * Date: 27.07.2017
 * Time: 14:25
 */

namespace SPHERE\Application\Api\Document\Standard\Repository;


use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

class GradebookOverview extends AbstractDocument
{

    /**
     * @return string
     */
    public function getName()
    {

        return 'Notenübersicht';
    }

    public function buildDocument($pageList = array())
    {
        $Document = (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if( Content.Person.Data.Name.First is not empty) %}
                                    {{ Content.Person.Data.Name.First }} {{ Content.Person.Data.Name.Last }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        )
                    )
                )
            )
        );

        return $Document;
    }
}