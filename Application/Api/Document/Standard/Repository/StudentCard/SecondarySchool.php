<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.03.2017
 * Time: 14:52
 */

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;

class SecondarySchool extends AbstractStudentCard
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Schülerkartei - Mittelschule';
    }

    /**
     * @return int
     */
    public function getTypeId(): int
    {
        return ($tblType = $this->getType()) ? $tblType->getId() : 0;
    }

    /**
     * @return false|TblType
     */
    public function getType()
    {
        return Type::useService()->getTypeByShortName('OS');
    }

    /**
     * @return Page
     */
    public function buildPage(): Page
    {
        $SmallTextSize = '7px';
        $InputText = '12px';
        $thicknessOutLines = '1.2px';
        $thicknessInnerLines = '0.5px';

        $subjectPosition = array();
        $Page = new Page();
        $Page->addSlice($this->setLetterRow());
        $Page->addSlice((new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Mittelschule')
                    ->styleHeight('30px')
                    ->styleTextSize('18px')
                    ->styleTextBold()
                    ->stylePaddingTop('7px')
                    ->stylePaddingLeft('5px')
                    ->styleBorderLeft($thicknessOutLines)
                    ->styleBorderTop($thicknessOutLines)
                    ->styleBorderRight($thicknessInnerLines)
                    , '18%')
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Name')
                            ->stylePaddingLeft('4px')
                            ->styleTextSize($SmallTextSize)
                            ->styleBorderTop($thicknessOutLines)
                            , '40%')
                        ->addElementColumn((new Element())
                            ->setContent('Vorname')
                            ->stylePaddingLeft('4px')
                            ->styleTextSize($SmallTextSize)
                            ->styleBorderTop($thicknessOutLines)
                            ->styleBorderRight($thicknessOutLines)
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
                            ->styleBorderRight($thicknessOutLines)
                            ->styleHeight('24.5px')
                            , '40%')
                    )
                )
            )
        );
        $Page->addSlice((new Slice())
            ->addSection((new Section())
                ->addSliceColumn(
                    $this->setCheckBox(
                        '{% if( Content.Student.Course.Degree.Main is not empty) %}
                                    {{ Content.Student.Course.Degree.Main }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                    )
                        ->styleBorderLeft($thicknessOutLines)
                        ->styleBorderTop($thicknessInnerLines)
                        ->styleBorderBottom($thicknessOutLines)
                    , '4%')
                ->addElementColumn((new Element())
                    ->setContent('Hauptschulabschluss')
                    ->styleHeight('20px')
                    ->stylePaddingTop('4px')
                    ->styleBorderTop($thicknessInnerLines)
                    ->styleBorderBottom($thicknessOutLines)
                    , '15%')
                ->addSliceColumn(
                    $this->setCheckBox(
                        '{% if( Content.Student.Course.Degree.Real is not empty) %}
                                    {{ Content.Student.Course.Degree.Real }}
                                {% else %}
                                    &nbsp;
                                {% endif %}'
                    )
                        ->styleBorderTop($thicknessInnerLines)
                        ->styleBorderBottom($thicknessOutLines)
                    , '4%')
                ->addElementColumn((new Element())
                    ->setContent('Realschulabschluss')
                    ->styleHeight('20px')
                    ->stylePaddingTop('4px')
                    ->styleBorderTop($thicknessInnerLines)
                    ->styleBorderBottom($thicknessOutLines)
                    ->styleBorderRight($thicknessOutLines)
                )
            )
        );
        $Page->addSlice((new Slice())
            ->addElement((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
            )
        );
        $SliceArrayGradeLayoutHeader = $this->setGradeLayoutHeader($subjectPosition, 19, 6, 5, '190px', '-107px', '1.2px', '0.5px', '9px', true);
        $Page->addSliceArray($SliceArrayGradeLayoutHeader);
        $Page->addSliceArray($this->setGradeLayoutBody($subjectPosition, $this->getTypeId(), 19, 28, 9));

        return $Page;
    }

    /**
     * @param array  $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $part = '0')
    {

        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
            ->addPage($this->buildRemarkPage($this->getType() ? $this->getType() : null))
        );
    }
}