<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Section;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class FelsStyle
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\FELS
 */
class FelsStyle
{
    const TEXT_COLOR = '#0094CC';

    /**
     * @param $IsSample
     * @param $schoolTypeName
     *
     * @return Slice
     */
    public static function getHeader($IsSample, $schoolTypeName)
    {
        $slice =
            (new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('Freies Evangelisches Limbacher Schulzentrum')
                        ->styleTextBold()
                        ->styleTextSize('22px')
                        ->styleTextColor(self::TEXT_COLOR)
                        ->styleMarginTop('20px')
                    )
                );
//                ->addSection((new Section())
//                    ->addElementColumn((new Element())
//                        ->setContent($schoolTypeName)
//                        ->styleTextBold()
//                        ->styleTextSize('22px')
//                        ->styleMarginTop('5px')
//                        ->styleTextColor(self::TEXT_COLOR)
//                    )
//                );
        // Sample
        if($IsSample){
            $slice
                ->addSection((new Section())
                    ->addElementColumn((new Element\Sample())
                        ->styleTextSize('30px')
                        ->styleMarginTop('5px')
                    )
                );
        }

        $section = new Section();
        $section->addSliceColumn($slice, '75%');

        $section
            ->addElementColumn((new Element\Image('/Common/Style/Resource/Logo/FELS.jpg', 'auto', '66px'))->styleAlignRight(), '25%');

        return
            (new Slice())
                ->addSection($section)
                ->stylePaddingTop('24px')
                ->styleHeight('80px');
    }

    /**
     * @param $personId
     * @param string $height
     * @param string $marginTop
     * @param bool $hasTeam
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     *
     * @return Slice
     */
    public static function getCustomDescription($personId, string $height = '100px', string $marginTop = '10px', bool $hasTeam = true,
        ?TblPrepareCertificate $tblPrepareCertificate = null): Slice
    {
        $tblSetting = Consumer::useService()->getSetting('Education', 'Certificate', 'Generator', 'IsDescriptionAsJustify');

        $slice = (new Slice())
            ->styleMarginTop($marginTop)
            ->styleHeight($height)
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Fehltage entschuldigt:')
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Missing is not empty) %}
                                        {{ Content.P' . $personId . '.Input.Missing }}
                                    {% else %}
                                        &nbsp;
                                    {% endif %}')
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('unentschuldigt:')
                    ->styleAlignRight()
                    , '25%')
                ->addElementColumn((new Element())
                    ->setContent('{% if(Content.P' . $personId . '.Input.Bad.Missing is not empty) %}
                                        {{ Content.P' . $personId . '.Input.Bad.Missing }}
                                    {% else %}
                                        &nbsp;
                                    {% endif %}')
                    ->styleAlignCenter()
                    , '10%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleAlignCenter()
                )
        );

        if ($hasTeam
            && ($tblPerson = Person::useService()->getPersonById($personId))
            && $tblPrepareCertificate
            && ($tblPrepareInformation = Prepare::useService()->getPrepareInformationBy($tblPrepareCertificate, $tblPerson, 'Team'))
            && $tblPrepareInformation->getValue()
        ) {
            $slice->addElement((new Element())
                ->styleMarginTop('5px')
                ->setContent('Arbeitsgemeinschaften: ' . $tblPrepareInformation->getValue())
            );
        }

        $element = (new Element())
            ->styleMarginTop('5px')
            ->setContent('Bemerkungen: {% if(Content.P'.$personId.'.Input.RemarkWithoutTeam is not empty) %}
                                {{ Content.P'.$personId.'.Input.RemarkWithoutTeam|nl2br }}
                            {% else %}
                                ---
                            {% endif %}');
        if ($tblSetting && $tblSetting->getValue()) {
            $element->styleAlignJustify();
        }

        $slice->addSection((new Section())
            ->addElementColumn($element)
        );

        return $slice;
    }
}