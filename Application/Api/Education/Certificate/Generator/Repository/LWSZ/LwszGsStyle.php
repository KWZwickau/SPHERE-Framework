<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\LWSZ;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class LwszGsStyle
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\Lwsz
 */
class LwszGsStyle
{
    /**
     * @param $IsSample
     *
     * @return Slice
     */
    public static function getHeader($IsSample)
    {
        $height = '66px';
        $heightIndiv = '100px';
        $width = '214px';

        $slice = new Slice();
        $section = new Section();

        // Individually Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/LWSZ.jpg', 'auto', $heightIndiv)), '39%');

        // Sample
        if($IsSample){
            $section->addElementColumn((new Element\Sample())->styleTextSize('30px'));
        } else {
            $section->addElementColumn((new Element()), '22%');
        }

        // Standard Logo
        $section->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/ClaimFreistaatSachsen.jpg',
            $width, $height))
            ->styleAlignRight()
            , '39%');

        $slice->stylePaddingTop('24px');
        $slice->styleHeight('100px');
        $slice->addSection($section);

        return $slice;
    }

    /**
     * @param $personId
     *
     * @return Slice
     */
    public static function getMissing($personId)
    {

        $Slice = new Slice();
        $Slice->addSection((new Section())
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
        )->styleMarginTop('15px');

        return $Slice;
    }

    /**
     * @param string $MarginTop
     *
     * @return Slice
     */
    public static function getParentSign($MarginTop = '25px')
    {
        $ParentSlice = (new Slice());
        $ParentSlice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Zur Kenntnis genommen:')
                , '30%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '60%')
            ->addElementColumn((new Element())
                , '10%')
        )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '30%')
                ->addElementColumn((new Element())
                    ->setContent('Eltern')
                    ->styleAlignCenter()
                    ->styleTextSize('11px')
                    , '60%')
                ->addElementColumn((new Element())
                    , '10%')
            )
            ->styleMarginTop($MarginTop);
        return $ParentSlice;
    }

    /**
     * @param Certificate $certificate
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public static function buildSecondPage(Certificate $certificate, TblPerson $tblPerson = null)
    {
        $personId = $tblPerson ? $tblPerson->getId() : 0;

        return (new Page())
            ->addSlice((new Slice())
                ->addElement((new Element\Image('/Common/Style/Resource/Logo/LWSZ.jpg',
                    '', '100px'))
                    ->styleAlignRight()
                    ->styleMarginTop('24px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('
                        Zwenkau, 
                        {% if(Content.P'.$personId.'.Input.Date is not empty) %}
                            {{ Content.P'.$personId.'.Input.Date }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleAlignRight()
                    ->styleTextSize('12pt')
                    ->styleMarginTop('20px')
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.StudentLetter is not empty) %}
                            {{ Content.P' . $personId . '.Input.StudentLetter|nl2br }}
                        {% else %}
                            &nbsp;
                        {% endif %}')
                    ->styleTextSize('12pt')
                    ->styleMarginTop('20px')
                    ->styleAlignJustify()
//                    ->styleHeight('745px')
                    ->styleHeight('804px')
                )
            )
//            ->addSlice($certificate->getSignPart($personId, false))
            ->addSlice(self::buildFooter($certificate, $tblPerson));
    }

    /**
     * @param Certificate $certificate
     * @param TblPerson|null $tblPerson
     * @param string $marginTop
     *
     * @return Slice
     */
    public static function buildFooter(Certificate $certificate, TblPerson $tblPerson = null)
    {
        if ($tblPerson) {
            $personId = $tblPerson->getId();
        } else {
            $personId = 0;
        }

        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom()
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
                    ->setContent(
                        ($tblPerson ? $tblPerson->getFullName() : '') . ' | '
                        . (($tblStudentEducation = $certificate->getTblStudentEducation()) && ($tblDivisionCourse = $tblStudentEducation->getTblDivision())
                            ? 'Klasse ' . $tblDivisionCourse->getName() : '')
                        . ' | '
                        . (strpos($certificate->getCertificateName(), 'info') ? 'Halbjahresinformation' : 'Jahreszeugnis')
                    )
                    ->styleTextSize('9.5px')
//                    ->styleHeight('0px')
                    , '70%')
                    ->addElementColumn((new Element())
                        ->setContent('
                            {% if(Content.P' . $personId . '.DivisionTeacher.Description is not empty) %}
                                    {{ Content.P' . $personId . '.DivisionTeacher.Description }}
                                {% else %}
                                    Klassenlehrer(in)
                                {% endif %}
                            ')
                    ->styleAlignCenter()
                    ->styleTextSize('9.5px')
                    , '30%')
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    , '70%')
                ->addElementColumn((new Element())
                    ->setContent(
                        '{% if(Content.P' . $personId . '.DivisionTeacher.Name is not empty) %}
                                {{ Content.P' . $personId . '.DivisionTeacher.Name }}
                            {% else %}
                                &nbsp;
                            {% endif %}'
                    )
                    ->styleTextSize('9.5px')
                    ->stylePaddingTop('2px')
                    ->styleAlignCenter()
                    , '30%')
            )
            ;

    }
}