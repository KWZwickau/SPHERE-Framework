<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CosHjPri
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class CosHjPri extends Certificate
{

    const TEXT_SIZE = '13px';
    const TEXT_FAMILY = 'Trebuchet MS';

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gradeLanesSlice = $this->getGradeLanesCoswig($personId, self::TEXT_SIZE, false, '25px');
        $subjectLanesSlice = $this->getSubjectLanesCoswig($personId, true, array(), self::TEXT_SIZE,
            false);

        return $this->buildContentPage($personId, $this->isSample(), 'Halbjahresinformation der Grundschule',
            '1. Schulhalbjahr', $gradeLanesSlice, $subjectLanesSlice, true
        );
    }

    /**
     * @param $personId
     * @param $isSample
     * @param string $title
     * @param $term
     * @param Slice $gradeLanesSlice
     * @param Slice $subjectLanesSlice
     * @param bool $isInformation
     *
     * @return Page
     */
    public static function buildContentPage(
        $personId,
        $isSample,
        $title,
        $term,
        $gradeLanesSlice,
        $subjectLanesSlice,
        bool $isInformation
    ) {

        $subjectLanesSlice->styleHeight('185px');

        if ($isSample) {
            $Header = array(
                (new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSC.jpg',
                                '100px', '100px'))
                                ->stylePaddingTop('12px')
                                ->styleHeight('0px')
                                ->styleAlignCenter()
                                , '25%')
                            ->addElementColumn((new Element())
                                ->setContent('FREISTAAT SACHSEN')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize('21px')
                                ->styleAlignCenter()
                                ->stylePaddingTop('22px')
                                , '50%')
                            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                                '165px', '50px'))
                                ->stylePaddingTop('12px')
                                , '25%')
                        )
                    ),
                (new Section())
                    ->addSliceColumn((new Slice())
                        ->addSection((new Section())
                            ->addElementColumn((new Element\Sample())
                                ->styleTextSize('30px')
                                ->styleMarginTop('55px')
                                ->styleHeight('0px')
                            )
                            ->addElementColumn((new Element())
                                ->setContent('Evangelische Schule Coswig')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize('21px')
                                ->styleTextBold()
                                ->styleAlignCenter()
                                ->styleMarginTop('40px')
                                ->styleLineHeight('85%')
                                , '50%')
                            ->addElementColumn((new Element())
                                , '25%')
                        )
                    )
            );
        } else {
            $Header = array(
                (new Section())
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/EVSC.jpg',
                        '100px', '100px'))
                        ->stylePaddingTop('12px')
                        ->styleHeight('20px')
                        ->styleAlignCenter()
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('FREISTAAT SACHSEN')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextSize('21px')
                        ->styleAlignCenter()
                        ->stylePaddingTop('22px')
                        , '50%')
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        ->stylePaddingTop('12px')
                        , '25%'),
                (new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Evangelische Schule Coswig')
                        ->styleFontFamily(self::TEXT_FAMILY)
                        ->styleTextSize('21px')
                        ->styleTextBold()
                        ->styleAlignCenter()
                        ->styleMarginTop('40px')
                        ->styleLineHeight('85%')
                    )
            );
        }

        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn((new Slice())
                        ->addSectionList($Header)
                        ->addElement((new Element())
                            ->setContent('staatlich anerkannte Ersatzschule')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleTextSize('16px')
                            ->styleAlignCenter()
                        )
                        ->addElement((new Element())
                            ->setContent($title)
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleTextSize('20px')
                            ->styleTextBold()
                            ->styleAlignCenter()
                            ->styleMarginTop('30px')
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Klasse')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '7%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Name }}')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '10%')
                                    ->addElementColumn((new Element())
                                        ->setContent('')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '57%')
                                    ->addElementColumn((new Element())
                                        ->setContent($term)
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '16%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '10%')
                                )->styleMarginTop('45px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Vor- und Zuname:')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '18%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '64%')
                                    ->addElementColumn((new Element())
                                        ->setContent('')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '18%')
                                )->styleMarginTop('35px')
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($gradeLanesSlice)
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addElement((new Element())
                                    ->setContent('Leistung in den einzelnen Fächern')
                                    ->styleFontFamily(self::TEXT_FAMILY)
                                    ->styleTextBold()
                                    ->styleTextItalic()
                                    ->styleMarginTop('30px')
                                    ->styleTextSize(self::TEXT_SIZE)
                                )
                            )
                        )
                        ->addSection((new Section())
                            ->addSliceColumn($subjectLanesSlice)
                        )
                        ->addElement((new Element())
                            ->setContent('Bemerkungen:')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleTextItalic()
                            ->styleTextSize(self::TEXT_SIZE)
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                                {% else %}
                                                    
                                                {% endif %}')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleLineHeight('85%')
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '85%')
                                )->styleHeight('95px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Fehltage entschuldigt:')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize(self::TEXT_SIZE)
                                , '22%')
                            ->addElementColumn((new Element())
                                ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Missing }}
                                        {% else %}
                                            
                                        {% endif %}')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize(self::TEXT_SIZE)
                                , '7%')
                            ->addElementColumn((new Element())
                                , '5%')
                            ->addElementColumn((new Element())
                                ->setContent('unentschuldigt:')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize(self::TEXT_SIZE)
                                , '15%')
                            ->addElementColumn((new Element())
                                ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                            {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                        {% else %}
                                            
                                        {% endif %}')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize(self::TEXT_SIZE)
                                , '7%')
                            ->addElementColumn((new Element())
                                , '44%')
                        )
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Datum:')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '7%')
                                    ->addElementColumn((new Element())
                                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                                {{ Content.P' . $personId . '.Input.Date }}
                                            {% else %}
                                                
                                            {% endif %}')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleHeight('20px')
                                        ->styleBorderBottom()
                                        ->styleAlignCenter()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '20%')
                                    ->addElementColumn((new Element())
                                        , '56%')
                                )
                                ->styleMarginTop('30px')
                            )
                        )
                        ->addSection(self::getSignSection($personId, $isInformation))
                        ->addSection((new Section())
                            ->addSliceColumn((new Slice())
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Zur Kenntnis genommen:')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '25%')
                                    ->addElementColumn((new Element())
                                        ->setContent('')
                                        ->styleHeight('16px')
                                        ->styleBorderBottom()
                                        ->styleTextSize(self::TEXT_SIZE)
                                        , '75%')
                                )
                                ->addSection((new Section())
                                    ->addElementColumn((new Element())
                                        ->setContent('Personensorgeberechtigte/r')
                                        ->styleFontFamily(self::TEXT_FAMILY)
                                        ->styleAlignCenter()
                                        ->styleTextSize('11px')
                                        , '100%')
                                )
                                ->styleMarginTop('30px')
                            )
                        )
                        ->addSection((new Section())
                            ->addElementColumn((new Element())
                                ->setContent('Notenstufen 1 = sehr gut, 2 = gut, 3 = befriedigend, 4 = ausreichend, 5 = mangelhaft, 6 = ungenügend')
                                ->styleFontFamily(self::TEXT_FAMILY)
                                ->styleTextSize('9px')
                                ->styleMarginTop('20px')
                            )
                        )
                    )
                )->styleBorderAll()
                ->stylePaddingLeft('20px')
                ->stylePaddingRight('20px')
            );
    }

    /**
     * @param int $personId
     * @param bool $isInformation
     *
     * @return Section
     */
    private static function getSignSection(int $personId, bool $isInformation) : Section
    {
        if ($isInformation) {
            return (new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '65%')
                        ->addElementColumn((new Element())
                            ->setContent('')
                            ->styleHeight('16px')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '65%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenleiter/in
                                {% endif %}
                            ')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleTextSize('11px')
                            , '35%')
                    )
                        ->addSection((new Section())
                        ->addElementColumn((new Element())
                            , '65%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                {% else %}
                                    
                                {% endif %}')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleLineHeight('85%')
                            ->styleTextSize('11px')
                            ->stylePaddingBottom('3px')
                            , '35%')
                    )
                    ->styleMarginTop('30px')
                );
        } else {
            return (new Section())
                ->addSliceColumn((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('')
                            ->styleHeight('16px')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('')
                            ->styleHeight('16px')
                            ->styleBorderBottom()
                            ->styleAlignCenter()
                            ->styleTextSize(self::TEXT_SIZE)
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('
                                    {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                        {{ Content.P' . $personId . '.Headmaster.Description }}
                                    {% else %}
                                        Schulleiter/in
                                    {% endif %}
                                ')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleTextSize('11px')
                            , '35%'
                        )
                        ->addElementColumn((new Element())
                            , '30%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('
                                    {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                        {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                    {% else %}
                                        Klassenleiter/in
                                    {% endif %}
                                ')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleTextSize('11px')
                            , '35%')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                        {{ Content.P' . $personId . '.Headmaster.Name }}
                                    {% else %}
                                        
                                    {% endif %}'
                            )
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleLineHeight('85%')
                            ->styleTextSize('11px')
                            ->stylePaddingBottom('3px')
                            , '35%')
                        ->addElementColumn((new Element())
                            , '30%')
                        ->addElementColumn((new Element())
                            ->setContent('{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                        {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                    {% else %}
                                        
                                    {% endif %}')
                            ->styleFontFamily(self::TEXT_FAMILY)
                            ->styleLineHeight('85%')
                            ->styleTextSize('11px')
                            ->stylePaddingBottom('3px')
                            , '35%')
                    )
                    ->styleMarginTop('30px')
             );
        }
    }
}
