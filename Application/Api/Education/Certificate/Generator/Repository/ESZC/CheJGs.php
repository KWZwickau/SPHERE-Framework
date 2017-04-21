<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\ESZC;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class CheJGs
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository
 */
class CheJGs extends Certificate
{

    const TEXT_SIZE = '12pt';

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null)
    {

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        if ($this->isSample()) {
            $Header = (new Slice())
                ->addSection((new Section())
//                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ChemnitzLogo.jpg',
//                        '60px', '60px'))
//                        , '25%')
                    ->addElementColumn((new Element()), '25%')
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                    )
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        ->styleMarginTop('4px')
                        ->styleAlignRight()
                        , '25%')
                );
        } else {
            $Header = (new Slice())
                ->addSection((new Section())
//                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ChemnitzLogo.jpg',
//                        '60px', '60px'))
//                        , '25%')
                    ->addElementColumn((new Element()), '25%')
                    ->addElementColumn((new Element()))
                    ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
                        '165px', '50px'))
                        , '25%')
                );
        }

        return (new Page())
            ->addSlice(
                $Header
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Company.Data.Name is not empty) %}
                                {{ Content.P' . $personId . '.Company.Data.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}
                        ')
                    ->styleBorderBottom()
                    ->styleAlignCenter()
                    ->styleTextSize('17px')
                    ->styleTextBold()
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Name der Schule')
                    ->styleAlignCenter()
                    ->styleTextSize('13px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Jahreszeugnis der Grundschule')
                    ->styleTextSize('24px')
                    ->styleTextBold()
                    ->styleAlignCenter()
                    ->styleMarginTop('30px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Klasse')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '10%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Level.Name }}{{ Content.P' . $personId . '.Division.Data.Name }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '10%')
                    ->addElementColumn((new Element())
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Schuljahr&nbsp;&nbsp;')
                        ->styleAlignRight()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '20%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Division.Data.Year }}')
                        ->styleBorderBottom()
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '15%')
                )->styleMarginTop('55px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Vorname und Name:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{{ Content.P' . $personId . '.Person.Data.Name.First }}
                                          {{ Content.P' . $personId . '.Person.Data.Name.Last }}')
                        ->styleBorderBottom()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '75%')
                )->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('10px')
                )
            )
            ->addSlice($this->getGradeLanesCustomForChemnitz($personId, self::TEXT_SIZE, false))
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Einschätzung:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '16%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Rating is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Rating }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize('11pt')
                        ->styleHeight('50px')
                        , '84%')
                )
                ->styleMarginTop('15px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('Leistungen in den einzelnen Fächern:')
                    ->styleMarginTop('20px')
                    ->styleTextBold()
                    ->styleTextSize(self::TEXT_SIZE)
                )
            )
            ->addSlice($this->getSubjectLanesCustomForChemnitz($personId, true, array(), self::TEXT_SIZE, false))
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('15px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Bemerkungen:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '18%')
                    ->addElementColumn((new Element())
                        ->setContent('Fehltage entschuldigt:')
                        ->styleBorderBottom('1px')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Missing }}
                                {% else %}
                                    0
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '12%')
                    ->addElementColumn((new Element())
                        ->setContent('unentschuldigt:')
                        ->styleBorderBottom('1px')
                        ->styleAlignRight()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '15%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                {% else %}
                                    0
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '12%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom('1px')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                    )
                )
                ->styleMarginTop('25px')
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('20px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Remark is not empty) %}
                                    {{ Content.P' . $personId . '.Input.Remark|nl2br }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleHeight('100px')
                        ->styleTextSize('11pt')
                    )
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Versetzungsvermerk:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '25%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Transfer) %}
                                    {{ Content.P' . $personId . '.Input.Transfer }}
                                {% else %}
                                      &nbsp;
                                {% endif %}')
                        ->styleBorderBottom('1px')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '75%')
                )
                ->styleMarginTop('5px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Datum:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '7%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Input.Date is not empty) %}
                                                {{ Content.P' . $personId . '.Input.Date }}
                                            {% else %}
                                                &nbsp;
                                            {% endif %}')
                        ->styleBorderBottom('1px', '#000')
                        ->styleAlignCenter()
                        ->styleTextSize(self::TEXT_SIZE)
                        , '23%')
                    ->addElementColumn((new Element())
                        , '70%')
                )
                ->styleMarginTop('18px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleAlignCenter()
                        ->styleBorderBottom('1px', '#000')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.Headmaster.Description is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Description }}
                                {% else %}
                                    Schulleiter(in)
                                {% endif %}
                            ')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('Dienstsiegel der Schule')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                    ->addElementColumn((new Element())
                        , '5%')
                    ->addElementColumn((new Element())
                        ->setContent('
                                {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}'
                        )
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.Headmaster.Name is not empty) %}
                                    {{ Content.P' . $personId . '.Headmaster.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                    ->addElementColumn((new Element())
                        , '40%')
                    ->addElementColumn((new Element())
                        ->setContent('{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                                {% else %}
                                    &nbsp;
                                {% endif %}')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('2px')
                        ->styleAlignCenter()
                        , '30%')
                )
                ->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Zur Kenntnis genommen:')
                        ->styleTextSize(self::TEXT_SIZE)
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        ->styleBorderBottom()
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '30%')
                    ->addElementColumn((new Element())
                        ->setContent('Eltern')
                        ->styleAlignCenter()
                        ->styleTextSize('11px')
                        , '40%')
                    ->addElementColumn((new Element())
                        , '30%')
                )->styleMarginTop('20px')
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Noten: 1 = sehr gut; 2 = gut; 3 = befriedigend; 4 = ausreichend; 5 = mangelhaft;
                                          6 = ungenügend (6 = ungenügend nur bei der Bewertung der Leistungen)')
                        ->styleTextSize('9.5px')
                    )
                )->styleMarginTop('10px')
            );
    }
}
