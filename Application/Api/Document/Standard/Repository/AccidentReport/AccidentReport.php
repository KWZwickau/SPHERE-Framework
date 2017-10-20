<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\AccidentReport;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Text\Repository\Code;

/**
 * Class AccidentReport
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class AccidentReport extends AbstractDocument
{

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
//        echo new Code(print_r($DataPost, true));
//        exit;

        // school
        $this->FieldValue['School'] = (isset($DataPost['School']) && $DataPost['School'] != '' ? $DataPost['School'] : '&nbsp;');
        $this->FieldValue['SchoolExtended'] = (isset($DataPost['SchoolExtended']) && $DataPost['SchoolExtended'] != '' ? $DataPost['SchoolExtended'] : '&nbsp;');
        $this->FieldValue['SchoolAddressStreet'] = (isset($DataPost['SchoolAddressStreet']) && $DataPost['SchoolAddressStreet'] != '' ? $DataPost['SchoolAddressStreet'] : '&nbsp;');
        $this->FieldValue['SchoolAddressCity'] = (isset($DataPost['SchoolAddressCity']) && $DataPost['SchoolAddressCity'] != '' ? $DataPost['SchoolAddressCity'] : '&nbsp;');
        // responibility
        $this->FieldValue['SchoolResponsibility'] = (isset($DataPost['SchoolResponsibility']) && $DataPost['SchoolResponsibility'] != '' ? $DataPost['SchoolResponsibility'] : '&nbsp;');
        $this->FieldValue['CompanyNumber'] = (isset($DataPost['CompanyNumber']) && $DataPost['CompanyNumber'] != '' ? $DataPost['CompanyNumber'] : '&nbsp;');
        // letter target
        $this->FieldValue['AddressTarget'] = (isset($DataPost['AddressTarget']) && $DataPost['AddressTarget'] != '' ? $DataPost['AddressTarget'] : '&nbsp;');
        $this->FieldValue['TargetAddressStreet'] = (isset($DataPost['TargetAddressStreet']) && $DataPost['TargetAddressStreet'] != '' ? $DataPost['TargetAddressStreet'] : '&nbsp;');
        $this->FieldValue['TargetAddressCity'] = (isset($DataPost['TargetAddressCity']) && $DataPost['TargetAddressCity'] != '' ? $DataPost['TargetAddressCity'] : '&nbsp;');
        // student
        $this->FieldValue['LastFirstName'] = (isset($DataPost['LastFirstName']) && $DataPost['LastFirstName'] != '' ? $DataPost['LastFirstName'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressPLZ'] = (isset($DataPost['AddressPLZ']) && $DataPost['AddressPLZ'] != '' ? $DataPost['AddressPLZ'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        // common
        if (isset($DataPost['Gender']) && $DataPost['Gender'] == 'Männlich') {
            $this->FieldValue['Male'] = true;
        } elseif (isset($DataPost['Gender']) && $DataPost['Gender'] == 'Weiblich') {
            $this->FieldValue['Female'] = true;
        }
        $this->FieldValue['Nationality'] = (isset($DataPost['Nationality']) && $DataPost['Nationality'] != '' ? $DataPost['Nationality'] : '&nbsp;');
        // custody
        $this->FieldValue['Custody'] = (isset($DataPost['Custody']) && $DataPost['Custody'] != '' ? $DataPost['Custody'] : '&nbsp;');
        $this->FieldValue['CustodyAddress'] = (isset($DataPost['CustodyAddress']) && $DataPost['CustodyAddress'] != '' ? $DataPost['CustodyAddress'] : '&nbsp;');
        // accident
        $this->FieldValue['DeathAccidentYes'] = (isset($DataPost['DeathAccidentYes']) && $DataPost['DeathAccidentYes'] != '' ? 'X' : '');
        $this->FieldValue['DeathAccidentNo'] = (isset($DataPost['DeathAccidentNo']) && $DataPost['DeathAccidentNo'] != '' ? 'X' : '');
        $this->FieldValue['AccidentDate'] = (isset($DataPost['AccidentDate']) && $DataPost['AccidentDate'] != '' ? $DataPost['AccidentDate'] : '&nbsp;');
        $this->FieldValue['AccidentHour'] = (isset($DataPost['AccidentHour']) && $DataPost['AccidentHour'] != '' ? $DataPost['AccidentHour'] : '&nbsp;');
        $this->FieldValue['AccidentMinute'] = (isset($DataPost['AccidentMinute']) && $DataPost['AccidentMinute'] != '' ? $DataPost['AccidentMinute'] : '&nbsp;');
        $this->FieldValue['AccidentPlace'] = (isset($DataPost['AccidentPlace']) && $DataPost['AccidentPlace'] != '' ? $DataPost['AccidentPlace'] : '&nbsp;');
        $this->FieldValue['AccidentDescription'] = (isset($DataPost['AccidentDescription']) && $DataPost['AccidentDescription'] != '' ? $DataPost['AccidentDescription'] : '&nbsp;');
        $this->FieldValue['DescriptionActive'] = (isset($DataPost['DescriptionActive']) && $DataPost['DescriptionActive'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['DescriptionPassive'] = (isset($DataPost['DescriptionPassive']) && $DataPost['DescriptionPassive'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['AccidentBodyParts'] = (isset($DataPost['AccidentBodyParts']) && $DataPost['AccidentBodyParts'] != '' ? $DataPost['AccidentBodyParts'] : '&nbsp;');
        $this->FieldValue['AccidentType'] = (isset($DataPost['AccidentType']) && $DataPost['AccidentType'] != '' ? $DataPost['AccidentType'] : '&nbsp;');
        // breake time
        $this->FieldValue['BreakNo'] = (isset($DataPost['BreakNo']) && $DataPost['BreakNo'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['BreakYes'] = (isset($DataPost['BreakYes']) && $DataPost['BreakYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['BreakAt'] = (isset($DataPost['BreakAt']) && $DataPost['BreakAt'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['BreakDate'] = (isset($DataPost['BreakDate']) && $DataPost['BreakDate'] != '' ? $DataPost['BreakDate'] : '&nbsp;');
        $this->FieldValue['BreakHour'] = (isset($DataPost['BreakHour']) && $DataPost['BreakHour'] != '' ? $DataPost['BreakHour'] : '&nbsp;');
        $this->FieldValue['ReturnYes'] = (isset($DataPost['ReturnYes']) && $DataPost['ReturnYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['ReturnNo'] = (isset($DataPost['ReturnNo']) && $DataPost['ReturnNo'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['ReturnDate'] = (isset($DataPost['ReturnDate']) && $DataPost['ReturnDate'] != '' ? $DataPost['ReturnDate'] : '&nbsp;');
        // withness
        $this->FieldValue['WitnessInfo'] = (isset($DataPost['WitnessInfo']) && $DataPost['WitnessInfo'] != '' ? $DataPost['WitnessInfo'] : '&nbsp;');
        $this->FieldValue['EyeWitnessYes'] = (isset($DataPost['EyeWitnessYes']) && $DataPost['EyeWitnessYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['EyeWitnessNo'] = (isset($DataPost['EyeWitnessNo']) && $DataPost['EyeWitnessNo'] != '' ? 'X' : '&nbsp;');
        // doctor
        $this->FieldValue['Doctor'] = (isset($DataPost['Doctor']) && $DataPost['Doctor'] != '' ? $DataPost['Doctor'] : '&nbsp;');
        $this->FieldValue['DoctorAddress'] = (isset($DataPost['DoctorAddress']) && $DataPost['DoctorAddress'] != '' ? $DataPost['DoctorAddress'] : '&nbsp;');
        // time in school
        $this->FieldValue['LocalStartHour'] = (isset($DataPost['LocalStartHour']) && $DataPost['LocalStartHour'] != '' ? $DataPost['LocalStartHour'] : '&nbsp;');
        $this->FieldValue['LocalStartMinute'] = (isset($DataPost['LocalStartMinute']) && $DataPost['LocalStartMinute'] != '' ? $DataPost['LocalStartMinute'] : '&nbsp;');
        $this->FieldValue['LocalEndHour'] = (isset($DataPost['LocalEndHour']) && $DataPost['LocalEndHour'] != '' ? $DataPost['LocalEndHour'] : '&nbsp;');
        $this->FieldValue['LocalEndMinute'] = (isset($DataPost['LocalEndMinute']) && $DataPost['LocalEndMinute'] != '' ? $DataPost['LocalEndMinute'] : '&nbsp;');
        // last line
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');
        $this->FieldValue['LocalLeader'] = (isset($DataPost['LocalLeader']) && $DataPost['LocalLeader'] != '' ? $DataPost['LocalLeader'] : '&nbsp;');
        $this->FieldValue['Recall'] = (isset($DataPost['Recall']) && $DataPost['Recall'] != '' ? $DataPost['Recall'] : '&nbsp;');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Unfallbericht';
    }

    /**
     * @return string
     */
    private function getSchoolAddress()
    {

        $Address = $this->FieldValue['School']
            .($this->FieldValue['SchoolExtended'] ? '<br/>'.$this->FieldValue['SchoolExtended'] : '')
            .'<br/>'.$this->FieldValue['SchoolAddressStreet']
            .'<br/>'.$this->FieldValue['SchoolAddressCity']
            .(!$this->FieldValue['SchoolExtended'] ? '<br/>' : '');

        return $Address;
    }

    /**
     * @param string $DateString
     * @param int    $Part 1 => day, 2 => Month, 3 => Year
     *
     * @return Code|string
     */
    private function getDatePartString($DateString = '', $Part = 3)
    {
        $DatePart = '&nbsp;';
        if (preg_match('!^([0-9]{1,2})[.,;/\- ]([0-9]{1,2})[.,;/\- ]([0-9]{2,4})!', $DateString, $Match)) {
            if (isset($Match[$Part])) {
                $DatePart = $Match[$Part];
            }
        }
        return $DatePart;
    }

    /**
     *
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->styleBorderAll()
                    ->styleHeight('920px')
                    ->addSection((new Section())
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->styleHeight('46px')
                            )
                            ->addElement((new Element())
                                ->setContent('1 Name und Anschrift der Einrichtung (Tageseinrichtung, Schule, Hochschule)')
                                ->styleTextSize('11px')
                                ->stylePaddingLeft('5px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->getSchoolAddress())
                                ->styleHeight('100px')
                                ->stylePaddingLeft('20px')
                            )
                            ->addElement((new Element())
                                ->setContent('4 Empfänger')
                                ->styleTextSize('11px')
                                ->stylePaddingLeft('5px')
                                ->stylePaddingBottom('5px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->FieldValue['AddressTarget'])
                                ->stylePaddingBottom('10px')
                                ->styleTextBold()
                                ->stylePaddingLeft('20px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->FieldValue['TargetAddressStreet'])
                                ->stylePaddingBottom('10px')
                                ->styleTextBold()
                                ->stylePaddingLeft('20px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->FieldValue['TargetAddressCity'])
                                ->stylePaddingBottom()
                                ->styleTextBold()
                                ->stylePaddingLeft('20px')
                                ->styleHeight('50px')
                            )
                            , '60%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addElement((new Element())
                                ->setContent('UNFALLANZEIGE')
                                ->styleTextSize('24px')
                                ->styleTextBold()
                            )
                            ->addElement((new Element())
                                ->setContent('für Kinder in Tageseinrichtungen, Schüler, Studierende')
                                ->styleTextSize('12px')
                                ->styleHeight('40px')
                            )
                            ->addElement((new Element())
                                ->setContent('2 Träger der Einrichtung')
                                ->styleTextSize('11px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->FieldValue['SchoolResponsibility'])
                                ->styleTextSize('12px')
                                ->styleHeight('29px')
                            )
                            ->addElement((new Element())
                                ->setContent('3 Unternehmensnr. des Unfallversicherungsträgers')
                                ->styleTextSize('11px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->FieldValue['CompanyNumber'])
                                ->styleTextSize('12px')
                            )
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleTextSize('1px')
                            ->styleBorderBottom()
                        )
                    )
                    /////// Name Geburtstag
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('5 Name, Vorname des Versicherten')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '55%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('6 Geburtstag')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Jahr')
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            , '10%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['LastFirstName'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '55%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['Birthday'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '25%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->getDatePartString($this->FieldValue['Birthday'], 1))
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->getDatePartString($this->FieldValue['Birthday'], 2))
                            ->styleAlignCenter()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '5%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->getDatePartString($this->FieldValue['Birthday'], 3))
                            ->styleAlignCenter()
                            ->styleBorderBottom()
                            , '10%'
                        )
                    )
                    ///////// Adresse
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('7 Straße, Hausnummer')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Postleitzahl')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Ort')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['AddressStreet'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['AddressPLZ'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['AddressCity'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '45%'
                        )
                    )
                    /////// Meta & gesetzlicher Vertreter
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('8 Geschlecht')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '22%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('9 Staatsangehörigkeit')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('10 Name und Anschrift der gesetzlichen Vertreter')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '60%'
                        )
                    )
                    ->addSection((new Section())
                        ->addSliceColumn(
                            $this->setCheckBox((isset($this->FieldValue['Male']) && $this->FieldValue['Male'] ? 'X' : ''))
                                ->styleBorderBottom()
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('männlich')
                            ->styleTextSize('12px')
                            ->styleHeight('25px')
                            ->stylePaddingTop('4px')
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addSliceColumn(
                            $this->setCheckBox((isset($this->FieldValue['Female']) && $this->FieldValue['Female'] ? 'X' : ''))
                                ->styleBorderBottom()
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('weiblich')
                            ->styleTextSize('12px')
                            ->styleHeight('25px')
                            ->stylePaddingTop('4px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['Nationality'])
                            ->stylePaddingLeft('5px')
                            ->styleHeight('27px')
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '18%'
                        )
                        ->addElementColumn((new Element())
                            // (Content.Person.Parent.Father.Address|length >= 38) Zählen der Länge entfällt
                            ->setContent($this->FieldValue['Custody']
                                .'<br/>'.$this->FieldValue['CustodyAddress'])
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->stylePaddingTop()
                            ->styleBorderBottom()
                            ->styleHeight('27px')
                            , '60%'
                        )
                    )
                    /////// Unfall Infos
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('11 Tödlicher Unfall')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('12 Unfallzeitpunkt')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '35%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('13 Unfallort (genaue Orts- und Straßenangabe mit PLZ)')
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            , '45%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->styleHeight('32px')
                            ->styleBorderBottom()
                            , '2%'
                        )
                        ->addSliceColumn(
                            $this->setCheckBox(($this->FieldValue['DeathAccidentYes'] ? 'X' : ''))
                                ->styleBorderBottom()
                                ->stylePaddingTop('3px')
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ja')
                            ->styleTextSize('12px')
                            ->styleHeight('24px')
                            ->stylePaddingTop('8px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addSliceColumn(
                            $this->setCheckBox((!$this->FieldValue['DeathAccidentNo'] ? 'X' : ''))
                                ->styleBorderBottom()
                                ->stylePaddingTop('3px')
                                ->styleHeight('29px')
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->styleTextSize('12px')
                            ->styleHeight('24px')
                            ->stylePaddingTop('8px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->styleHeight('32px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '2%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag <br/>'.$this->getDatePartString($this->FieldValue['AccidentDate'], 1))
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat <br/>'.$this->getDatePartString($this->FieldValue['AccidentDate'], 2))
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Jahr <br/>'.$this->getDatePartString($this->FieldValue['AccidentDate'], 3))
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Stunde <br/>'.$this->FieldValue['AccidentHour'])
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Minute <br/>'.$this->FieldValue['AccidentMinute'])
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('11px')
                            ->styleHeight('30px')
                            ->styleAlignCenter()
                            ->stylePaddingTop()
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['AccidentPlace'])
                            ->stylePaddingLeft('5px')
                            ->styleTextSize('12px')
                            ->stylePaddingTop()
                            ->styleBorderBottom()
                            ->styleHeight('30px')
                            , '45%'
                        )
                    )
                    ////// Schilderung des Unfallhergangs
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('14 Ausführliche Schilderung des Unfallhergangs (insbesondere Art der Veranstalltung,
                            bei Sportunfällen auch Sportart')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent(nl2br($this->FieldValue['AccidentDescription']))
                            ->styleHeight('200px')
                            ->stylePaddingLeft('20px')
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('Die Angaben beruhen auf der Schilderung')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleHeight('15px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '35%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionActive'])
                            ->styleHeight('20px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('des Versicherten')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleHeight('15px')
                            ->styleBorderBottom()
                            , '15%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionPassive'])
                            ->styleHeight('20px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('andere Personen')
                            ->styleTextSize('11px')
                            ->stylePaddingTop('5px')
                            ->styleHeight('15px')
                            ->styleBorderBottom()
                            , '15%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                            ->styleHeight('20px')
                            ->styleBorderBottom()
                            , '27%'
                        )
                    )
                    /////// Verletzungen
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('15 Verletzte Körperteile')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('16 Art der Verletzung')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '50%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['AccidentBodyParts'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '50%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['AccidentType'])
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '50%'
                        )
                    )
                    /////// Unterbrechung
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('17 Hat der Versicherte den Besuch der <br/> Einrichtung unterbrochen?')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '35%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['BreakNo'])
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['BreakYes'])
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('sofort')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['BreakAt'])
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('später am')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag <br/>'.$this->getDatePartString($this->FieldValue['BreakDate'], 1))
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat <br/>'.$this->getDatePartString($this->FieldValue['BreakDate'], 2))
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Stunde <br/>'.$this->FieldValue['BreakHour'])
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '7%'
                        )
                    )
                    /////// Vortsetzung
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('18 hat der Versicherte den Besuch der <br/> Einrichtung wieder aufgenommen?')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '50%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['ReturnNo'])
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['ReturnYes'])
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ja, am')
                            ->stylePaddingTop('3px')
                            ->styleHeight('24px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Tag <br/>'.$this->getDatePartString($this->FieldValue['ReturnDate'], 1))
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '6%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Monat <br/>'.$this->getDatePartString($this->FieldValue['ReturnDate'], 2))
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '7%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Jahr <br/>'.$this->getDatePartString($this->FieldValue['ReturnDate'], 3))
                            ->styleTextSize('11px')
                            ->styleAlignCenter()
                            ->styleHeight('27px')
                            ->styleBorderBottom()
                            , '7%'
                        )
                    )
                    /////// Kenntnis
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('19 Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift von Zeugen)')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '70%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('War diese Person Augenzeuge?')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '30%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['WitnessInfo'])
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('3px')
                            ->styleHeight('22px')
                            ->styleBorderBottom()
                            , '70%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['EyeWitnessYes'])
                            ->styleHeight('25px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('ja')
                            ->stylePaddingTop('3px')
                            ->styleHeight('22px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                        ->addSliceColumn($this->setCheckBox($this->FieldValue['EyeWitnessNo'])
                            ->styleHeight('25px')
                            ->styleBorderBottom()
                            , '4%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('nein')
                            ->stylePaddingTop('3px')
                            ->styleHeight('22px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderBottom()
                            , '11%'
                        )
                    )
                    /////// Kenntnis
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('20 Name und Anschrift des erstbehandelnden Arztes / Krankenhauses')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            ->styleBorderRight()
                            , '60%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('21 Beginn und Ende des Besuchs der Einrichtung')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['Doctor']
                                .'<br/>'.$this->FieldValue['DoctorAddress'])
                            ->stylePaddingLeft('5px')
                            ->styleHeight('42.3px')
                            ->styleBorderRight()
                            ->styleBorderBottom()
                            , '60%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Beginn')
                                    ->styleAlignCenter()
                                    ->styleTextSize('11px')
                                    ->stylePaddingLeft('5px')
                                    ->stylePaddingBottom()
                                    ->styleBorderRight()
                                    , '50%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Ende')
                                    ->styleAlignCenter()
                                    ->styleTextSize('11px')
                                    ->stylePaddingLeft('5px')
                                    ->stylePaddingBottom()
                                    , '50%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Stunde <br/>'.$this->FieldValue['LocalStartHour'])
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Minute <br/>'.$this->FieldValue['LocalStartMinute'])
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Stunde <br/>'.$this->FieldValue['LocalEndHour'])
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderRight()
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                                ->addElementColumn((new Element())
                                    ->setContent('Minute <br/>'.$this->FieldValue['LocalEndMinute'])
                                    ->styleTextSize('11px')
                                    ->styleAlignCenter()
                                    ->stylePaddingLeft('5px')
                                    ->styleBorderBottom()
                                    , '25%'
                                )
                            )
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['Date'])
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('22px')
                            ->styleHeight('18px')
                            ->styleBorderBottom()
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['LocalLeader'])
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('22px')
                            ->styleHeight('18px')
                            ->styleBorderBottom()
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent($this->FieldValue['Recall'])
                            ->stylePaddingLeft('5px')
                            ->stylePaddingTop('22px')
                            ->styleHeight('18px')
                            ->styleBorderBottom()
                            , '40%'
                        )
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('22 Datum')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '20%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Leiter (Beauftragter) der Einrichtung')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '40%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('Telefon-Nr. für Rückfragen (Ansprechpartner)')
                            ->styleTextSize('11px')
                            ->stylePaddingLeft('5px')
                            , '40%'
                        )
                    )
                )
            )
        );
    }

}