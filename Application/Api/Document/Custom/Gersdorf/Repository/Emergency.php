<?php
namespace SPHERE\Application\Api\Document\Custom\Gersdorf\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;

/**
 * Class Emergency
 *
 * @package SPHERE\Application\Api\Document\Custom\Zwickau\Repository
 */
class Emergency extends AbstractDocument
{

    const TEXT_SIZE = '11pt';
    const TEXT_SIZE_SMALL = '9pt';

    /**
     * AccidentReport constructor.
     *
     * @param array $Data
     */
    function __construct($Data)
    {

        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private $FieldValue = array();

    /**
     * @param $DataPost
     *
     * @return $this
     */
    private function setFieldValue($DataPost)
    {

        //getPerson
        $this->FieldValue['PersonId'] = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
//        $this->FieldValue['Gender'] = false;
//        if ($PersonId) {
//            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
//                //get Gender
//                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
//                    if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
//                        if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
//                            $this->FieldValue['Gender'] = $tblCommonGender->getName();
//                        }
//                    }
//                }
//            }
//        }

        // student
        $this->FieldValue['LastFirstName'] = (isset($DataPost['LastFirstName']) && $DataPost['LastFirstName'] != '' ? $DataPost['LastFirstName'] : '&nbsp;');
        $this->FieldValue['Gender'] = (isset($DataPost['Gender']) && $DataPost['Gender'] != '' ? $DataPost['Gender'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['Birthplace'] = (isset($DataPost['Birthplace']) && $DataPost['Birthplace'] != '' ? $DataPost['Birthplace'] : '&nbsp;');
        $this->FieldValue['AddressPLZCity'] = (isset($DataPost['AddressPLZCity']) && $DataPost['AddressPLZCity'] != '' ? $DataPost['AddressPLZCity'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['Nationality'] = (isset($DataPost['Nationality']) && $DataPost['Nationality'] != '' ? $DataPost['Nationality'] : '&nbsp;');
        $this->FieldValue['Disease'] = (isset($DataPost['Disease']) && $DataPost['Disease'] != '' ? $DataPost['Disease'] : '&nbsp;');
        $this->FieldValue['Phone'] = (isset($DataPost['Phone']) && $DataPost['Phone'] != '' ? $DataPost['Phone'] : '&nbsp;');

        // custody S1
        $this->FieldValue['S1LastFirstName'] = (isset($DataPost['S1']['LastFirstName']) && $DataPost['S1']['LastFirstName'] != '' ? $DataPost['S1']['LastFirstName'] : '&nbsp;');
        $this->FieldValue['S1Address'] = (isset($DataPost['S1']['Address']) && $DataPost['S1']['Address'] != '' ? $DataPost['S1']['Address'] : '&nbsp;');
        $this->FieldValue['S1Phone'] = (isset($DataPost['S1']['Phone']) && $DataPost['S1']['Phone'] != '' ? $DataPost['S1']['Phone'] : '&nbsp;');
        // custody S2
        $this->FieldValue['S2LastFirstName'] = (isset($DataPost['S2']['LastFirstName']) && $DataPost['S2']['LastFirstName'] != '' ? $DataPost['S2']['LastFirstName'] : '&nbsp;');
        $this->FieldValue['S2Address'] = (isset($DataPost['S2']['Address']) && $DataPost['S2']['Address'] != '' ? $DataPost['S2']['Address'] : '&nbsp;');
        $this->FieldValue['S2Phone'] = (isset($DataPost['S2']['Phone']) && $DataPost['S2']['Phone'] != '' ? $DataPost['S2']['Phone'] : '&nbsp;');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Dokument Notarzt';
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
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'5%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleHeight('25px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Evangelische Oberschule Gersdorf<br/>Schülerbogen')
                                    ->styleTextSize('15pt')
                                    ->styleTextBold()
                                    ->stylePaddingTop('5px')
                                    ->stylePaddingBottom('5px')
                                    ->styleBorderAll()
                                    ->styleAlignCenter()
                                    ->styleMarginBottom('50px')
                                )
                            )->stylePaddingBottom('5px')
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Name, Vorname')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['LastFirstName'])
                                , '30%')
                                ->addElementColumn((new Element())
                                    ->setContent('Geschlecht')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Gender'])
                                    ->stylePaddingBottom('5px')
                                , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Geburtsdatum')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Birthday'])
                                , '30%')
                                ->addElementColumn((new Element())
                                    ->setContent('Geb.-ort')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Birthplace'])
                                    ->stylePaddingBottom('5px')
                                , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Wohnort')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['AddressPLZCity'])
                                , '30%')
                                ->addElementColumn((new Element())
                                    ->setContent('Straße, Nr.')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['AddressStreet'])
                                    ->stylePaddingBottom('5px')
                                , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('')
                                , '50%')
                                ->addElementColumn((new Element())
                                    ->setContent('Staatsange-<br/>hörigkeit')
                                    ->styleTextBold()
                                , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Nationality'])
                                    ->stylePaddingBottom('50px')
                                , '30%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Behinderungen bzw. Krankheiten')
                                    ->styleTextBold()
                                    ->stylePaddingBottom('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Disease'])
                                    ->styleHeight('140px')
                                    ->stylePaddingBottom('18px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Sorgeberechtigte')
                                    ->styleTextSize('12pt')
                                    ->styleTextBold()
                                    ->stylePaddingBottom('15px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('1.')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Name, Vorname')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['S1LastFirstName'])
                                    ->styleBorderBottom()
                                    ->styleMarginBottom('10px')
                                , '75%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Anschrift')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['S1Address'])
                                    ->styleBorderBottom()
                                    ->styleMarginBottom('10px')
                                , '75%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Telefon')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['S1Phone'])
                                    ->styleBorderBottom()
                                    ->styleMarginBottom('10px')
                                , '75%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('2.')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Name, Vorname')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['S2LastFirstName'])
                                    ->styleBorderBottom()
                                    ->styleMarginBottom('10px')
                                , '75%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Anschrift')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['S2Address'])
                                    ->styleBorderBottom()
                                    ->styleMarginBottom('10px')
                                , '75%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Telefon')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['S2Phone'])
                                    ->styleBorderBottom()
                                    ->styleMarginBottom('10px')
                                , '75%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('3.')
                                    ->styleTextBold()
                                , '3%')
                                ->addElementColumn((new Element())
                                    ->setContent('Telefon Schüler')
                                    ->styleTextBold()
                                , '22%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Phone'])
                                    ->styleBorderBottom()
                                , '75%')
                            )
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'5%'
                        )
                    )
                )
            )
        );
    }
}