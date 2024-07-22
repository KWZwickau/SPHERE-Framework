<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\AccidentReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Extension\Extension;

/**
 * Class Style
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class Style extends Extension
{

    /**
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
        } elseif (isset($DataPost['Gender']) && $DataPost['Gender'] == 'Divers') {
            $this->FieldValue['Divers'] = true;
        } elseif (isset($DataPost['Gender']) && $DataPost['Gender'] == 'Without') {
            $this->FieldValue['Without'] = true;
        }
        $this->FieldValue['Nationality'] = (isset($DataPost['Nationality']) && $DataPost['Nationality'] != '' ? $DataPost['Nationality'] : '&nbsp;');
        // custody
        $this->FieldValue['CustodyAddress'] = (isset($DataPost['CustodyAddress']) && $DataPost['CustodyAddress'] != '' ? $DataPost['CustodyAddress'] : '&nbsp;');
        $this->FieldValue['Insurance'] = (isset($DataPost['Insurance']) && $DataPost['Insurance'] != '' ? $DataPost['Insurance'] : '&nbsp;');
        // accident
        $this->FieldValue['DeathAccidentYes'] = (isset($DataPost['DeathAccidentYes']) && $DataPost['DeathAccidentYes'] != '' ? 'X' : '');
        $this->FieldValue['DeathAccidentNo'] = (isset($DataPost['DeathAccidentNo']) && $DataPost['DeathAccidentNo'] != '' ? 'X' : '');
        $this->FieldValue['AccidentDate'] = (isset($DataPost['AccidentDate']) && $DataPost['AccidentDate'] != '' ? $DataPost['AccidentDate'] : '&nbsp;');
        $this->FieldValue['AccidentTime'] = (isset($DataPost['AccidentTime']) && $DataPost['AccidentTime'] != '' ? $DataPost['AccidentTime'] : '&nbsp;');
        $this->FieldValue['PhoneNumber'] = (isset($DataPost['PhoneNumber']) && $DataPost['PhoneNumber'] != '' ? $DataPost['PhoneNumber'] : '&nbsp;');
        $this->FieldValue['AccidentPlace'] = (isset($DataPost['AccidentPlace']) && $DataPost['AccidentPlace'] != '' ? $DataPost['AccidentPlace'] : '&nbsp;');
        $this->FieldValue['AccidentDescription'] = (isset($DataPost['AccidentDescription']) && $DataPost['AccidentDescription'] != '' ? $DataPost['AccidentDescription'] : '&nbsp;');
        $this->FieldValue['DescriptionActive'] = (isset($DataPost['DescriptionActive']) && $DataPost['DescriptionActive'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['DescriptionPassive'] = (isset($DataPost['DescriptionPassive']) && $DataPost['DescriptionPassive'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['DescriptionViolenceNo'] = (isset($DataPost['DescriptionViolenceNo']) && $DataPost['DescriptionViolenceNo'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['DescriptionViolenceYes'] = (isset($DataPost['DescriptionViolenceYes']) && $DataPost['DescriptionViolenceYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['AccidentBodyParts'] = (isset($DataPost['AccidentBodyParts']) && $DataPost['AccidentBodyParts'] != '' ? $DataPost['AccidentBodyParts'] : '&nbsp;');
        $this->FieldValue['AccidentType'] = (isset($DataPost['AccidentType']) && $DataPost['AccidentType'] != '' ? $DataPost['AccidentType'] : '&nbsp;');
        // breake time
        $this->FieldValue['BreakNo'] = (isset($DataPost['BreakNo']) && $DataPost['BreakNo'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['BreakYes'] = (isset($DataPost['BreakYes']) && $DataPost['BreakYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['BreakAt'] = (isset($DataPost['BreakAt']) && $DataPost['BreakAt'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['BreakDate'] = (isset($DataPost['BreakDate']) && $DataPost['BreakDate'] != '' ? $DataPost['BreakDate'] : '&nbsp;');
        $this->FieldValue['BreakTime'] = (isset($DataPost['BreakTime']) && $DataPost['BreakTime'] != '' ? $DataPost['BreakTime'] : '&nbsp;');
        $this->FieldValue['ReturnYes'] = (isset($DataPost['ReturnYes']) && $DataPost['ReturnYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['ReturnNo'] = (isset($DataPost['ReturnNo']) && $DataPost['ReturnNo'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['ReturnDate'] = (isset($DataPost['ReturnDate']) && $DataPost['ReturnDate'] != '' ? $DataPost['ReturnDate'] : '&nbsp;');
        // withness
        $this->FieldValue['WitnessInfo'] = (isset($DataPost['WitnessInfo']) && $DataPost['WitnessInfo'] != '' ? $DataPost['WitnessInfo'] : '&nbsp;');
        $this->FieldValue['EyeWitnessYes'] = (isset($DataPost['EyeWitnessYes']) && $DataPost['EyeWitnessYes'] != '' ? 'X' : '&nbsp;');
        $this->FieldValue['EyeWitnessNo'] = (isset($DataPost['EyeWitnessNo']) && $DataPost['EyeWitnessNo'] != '' ? 'X' : '&nbsp;');
        // doctor
        $this->FieldValue['DoctorAddress'] = (isset($DataPost['DoctorAddress']) && $DataPost['DoctorAddress'] != '' ? $DataPost['DoctorAddress'] : '&nbsp;');
        // time in school
        $this->FieldValue['LocalStartTime'] = (isset($DataPost['LocalStartTime']) && $DataPost['LocalStartTime'] != '' ? $DataPost['LocalStartTime'] : '&nbsp;');
        $this->FieldValue['LocalEndTime'] = (isset($DataPost['LocalEndTime']) && $DataPost['LocalEndTime'] != '' ? $DataPost['LocalEndTime'] : '&nbsp;');
        // last line
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');
        $this->FieldValue['LocalLeader'] = (isset($DataPost['LocalLeader']) && $DataPost['LocalLeader'] != '' ? $DataPost['LocalLeader'] : '&nbsp;');
        $this->FieldValue['Recall'] = (isset($DataPost['Recall']) && $DataPost['Recall'] != '' ? $DataPost['Recall'] : '&nbsp;');

        return $this;
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

    public function getHeaderSection()
    {

        return (new Section())
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->styleHeight('46px')
                )
                ->addElement((new Element())
                    ->setContent('<b>1</b> Name und Anschrift der Einrichtung')
                    ->styleTextSize('11px')
                    ->stylePaddingLeft('5px')
                )
                ->addElement((new Element())
                    ->setContent($this->getSchoolAddress())
                    ->styleHeight('100px')
                    ->stylePaddingLeft('20px')
                )
                ->addElement((new Element())
                    ->setContent('<b>4</b> Empfänger')
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
                    ->setContent('für Kinder in Tagesbetreuung oder vorschulischer Sprachförderung,
                                 Schülerinnen und Schüler, Studierende')
                    ->styleTextSize('14px')
                    ->styleLineHeight('95%')
                    ->styleHeight('50px')
                )
                ->addElement((new Element())
                    ->setContent('<b>2</b> Träger der Einrichtung')
                    ->styleTextSize('11px')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['SchoolResponsibility'])
                    ->styleTextSize('12px')
                    ->styleHeight('29px')
                )
                ->addElement((new Element())
                    ->setContent('<b>3</b> Unternehmensnr. des Unfallversicherungsträgers')
                    ->styleTextSize('11px')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['CompanyNumber'])
                    ->styleTextSize('12px')
                )
                , '40%'
            );
    }

    public function getBorderBottomSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
            ->setContent('&nbsp;')
            ->styleTextSize('1px')
            ->styleBorderBottom()
        );
    }

    public function getNameAndBirthdaySection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>5</b> Name, Vorname der versicherten Person')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            ->styleBorderRight()
            , '55%'
        )
        ->addElementColumn((new Element())
            ->setContent('<b>6</b> Geburtsdatum (TT.MM.JJJJ)')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            , '45%'
        );
    }

    public function getNameAndBirthdayDataSection()
    {

        return (new Section())
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
                ->styleBorderBottom()
                , '45%'
            );
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

    public function getAddressSection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>7</b> Straße, Hausnummer')
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
        );
    }

    public function getAddressDataSection()
    {

        return (new Section())
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
            );
    }

    public function getGenderSection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>8</b> Geschlecht')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            ->styleBorderRight()
            , '55%'
        )
        ->addElementColumn((new Element())
            ->setContent('<b>9</b> Staatsangehörigkeit')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            , '45%'
        );
    }

    public function getGenderDataSection()
    {

        return (new Section())
            ->addSliceColumn(
                $this->setCheckBox((isset($this->FieldValue['Male']) && $this->FieldValue['Male'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('22px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Männlich')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '9%'
            )
            ->addSliceColumn(
                $this->setCheckBox((isset($this->FieldValue['Female']) && $this->FieldValue['Female'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('22px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Weiblich')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '9%'
            )
            ->addSliceColumn(
                $this->setCheckBox((isset($this->FieldValue['Divers']) && $this->FieldValue['Divers'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('22px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Divers')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '9%'
            )
            ->addSliceColumn(
                $this->setCheckBox((isset($this->FieldValue['Without']) && $this->FieldValue['Without'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('22px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Keine Angabe')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->stylePaddingTop()
                ->styleBorderRight()
                ->styleBorderBottom()
                , '12%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Nationality'])
                ->stylePaddingLeft('5px')
                ->styleHeight('20px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '45%'
            );
    }

    public function getCustodySection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>10</b> Name, Anschrift und Telefonnummer der gesetzlich Vertretungsberechtigten')
            ->stylePaddingLeft('5px')
            ->styleTextSize('11px')
        );
    }

    public function getCustodyDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                // (Content.Person.Parent.Father.Address|length >= 38) Zählen der Länge entfällt
                ->setContent($this->FieldValue['CustodyAddress'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleHeight('14px')
            );
    }

    public function getInsuranceSection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>11</b> Krankenkasse (Name, PLZ, Ort, bei Familienversicherung Name des Mitglieds)')
            ->stylePaddingLeft('5px')
            ->styleTextSize('11px')
        );
    }

    public function getInsuranceDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Insurance'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleHeight('17px')
            );
    }

    /**
     * @param string $content
     * @param string $thicknessInnerLines
     *
     * @return Slice
     */
    protected function setCheckBox($content = '&nbsp;', $thicknessInnerLines = '0.5px')
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('5px')
                )
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('10px')
                    , '1.2%')
                ->addElementColumn((new Element())
                    ->setContent($content)
                    ->styleHeight('12px')
                    ->styleTextSize('8.5')
                    ->stylePaddingLeft('1.2px')
                    ->stylePaddingBottom('0px')
                    ->styleBorderAll($thicknessInnerLines)
                    , '1.6%')
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('10px')
                    , '1.2%')
            );
//            ->styleHeight('35px');
    }

    public function getDeadlyAccidentSection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>12</b> Tödlicher Unfall?')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            ->styleBorderRight()
            , '20%'
        )
        ->addElementColumn((new Element())
            ->setContent('<b>13</b> Unfallzeitpunkt (TT.MM.JJJJ/hh:mm)')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            , '80%'
        );
    }

    public function getDeadlyAccidentSectionTH()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>12</b> Tödlicher Unfall?')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            ->styleBorderRight()
            , '20%'
        )
        ->addElementColumn((new Element())
            ->setContent('<b>13</b> Unfallzeitpunkt (TT.MM.JJJJ/hh:mm)')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            ->styleBorderRight()
            , '35%'
        )
        ->addElementColumn((new Element())
            ->setContent('Telefonnummer der vers. Person / gesetzlichen Vertreters')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            , '45%'
        );
    }

    public function getDeadlyAccidentDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '2%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentNo'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('nein')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '4%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('ja')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->styleHeight('21px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '2%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentDate'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '21%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentTime'].' Uhr')
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleHeight('19px')
                , '59%'
            );
    }

    public function getDeadlyAccidentDataSectionTH()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '2%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentNo'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('nein')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '4%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('ja')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->styleHeight('21px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '2%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentDate'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '21%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentTime'].' Uhr')
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleBorderRight()
                ->styleHeight('19px')
                , '14%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['PhoneNumber'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleHeight('19px')
                , '45%'
            );
    }

    public function getAccidentLocationSection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>14</b> Unfallort (genaue Orts- und Straßenangabe mit PLZ)')
            ->stylePaddingLeft('5px')
            ->styleTextSize('11px')
            ->styleBorderRight()
            , '75%'
        )
        ->addElementColumn((new Element())
            ->setContent('<b>15</b> Unfall beim Distanzunterricht')
            ->stylePaddingLeft('5px')
            ->styleTextSize('11px')
            , '25%'
        );
    }

    public function getAccidentLocationDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentPlace'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                ->stylePaddingTop()
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleHeight('19px')
                , '75%'
            )
            ->addElementColumn((new Element())
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentNo'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('nein')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '4%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('ja')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '5%'
            );
    }

    public function getDescriptionSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>16</b> Ausführliche Schilderung des Unfallhergangs (insbesondere Art der Veranstalltung,
                            bei Sportunfällen auch Sportart)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
            );
    }

    public function getDescriptionDataSection($Height = '172px')
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent(nl2br($this->FieldValue['AccidentDescription']))
                ->styleHeight($Height)
                ->stylePaddingLeft('20px')
            );
    }

    public function getDescriptionInfoDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('Die Angaben beruhen auf der Schilderung')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
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
                ->setContent('der versicherten Person')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                ->styleBorderBottom()
                , '18%'
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
                ->styleHeight()
                ->styleBorderBottom()
                , '15%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '24%'
            );
    }

    public function getDescriptionInfoDataSectionTH()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('Die Angaben beruhen auf der Schilderung')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                ->stylePaddingLeft('5px')
                , '35%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionActive'])
                ->styleHeight('20px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('der versicherten Person')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                , '18%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionPassive'])
                ->styleHeight('20px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('andere Personen')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                , '15%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
                , '24%'
            );
    }

    public function getDescriptionViolenceDataSectionTH()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('Hat ein Gewaltereignis vorgelegen (körperlicher Übergriff, sexueller Übergriff)?')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '57%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionViolenceNo'])
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Nein')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                ->styleBorderBottom()
                , '10%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionViolenceYes'])
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight()
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '15%'
            );
    }

    public function getHurtSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>17</b> Verletzte Körperteile')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '50%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>18</b> Art der Verletzung')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '50%'
            );
    }

    public function getHurtDataSection()
    {

        return (new Section())
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
            );
    }

    public function getBreakSection()
    {

        return (new Section())
        ->addElementColumn((new Element())
            ->setContent('<b>19</b> Hat die Versicherte Person den <br/> Besuch der Einrichtung unterbrochen?')
            ->styleTextSize('11px')
            ->stylePaddingLeft('5px')
            ->styleHeight('27px')
            ->styleBorderBottom()
            , '35%'
        )
        ->addSliceColumn($this->setCheckBox($this->FieldValue['BreakNo'])
            ->stylePaddingTop()
            ->styleHeight('25px')
            ->styleBorderBottom()
            , '4%'
        )
        ->addElementColumn((new Element())
            ->setContent('Nein')
            ->styleTextSize('12px')
            ->stylePaddingTop('6px')
            ->styleHeight('21px')
            ->styleBorderBottom()
            , '7%'
        )
        ->addSliceColumn($this->setCheckBox($this->FieldValue['BreakYes'])
            ->stylePaddingTop()
            ->styleHeight('25px')
            ->styleBorderBottom()
            , '4%'
        )
        ->addElementColumn((new Element())
            ->setContent('Sofort')
            ->styleTextSize('12px')
            ->stylePaddingTop('6px')
            ->styleHeight('21px')
            ->styleBorderBottom()
            , '7%'
        )
        ->addSliceColumn($this->setCheckBox($this->FieldValue['BreakAt'])
            ->stylePaddingTop()
            ->styleHeight('25px')
            ->styleBorderBottom()
            , '4%'
        )
        ->addElementColumn((new Element())
            ->setContent('Später, am')
            ->styleTextSize('12px')
            ->stylePaddingTop('6px')
            ->styleHeight('21px')
            ->styleBorderBottom()
            , '9%'
        )
        ->addElementColumn((new Element())
            ->setContent($this->FieldValue['BreakDate'])
            ->stylePaddingTop('6px')
            ->stylePaddingLeft('6px')
            ->styleTextSize('12px')
            ->styleHeight('21px')
            ->styleBorderBottom()
            , '10%'
        )
        ->addElementColumn((new Element())
            ->setContent('(TT.MM) um')
            ->stylePaddingTop('4px')
            ->styleTextSize('12px')
            ->styleHeight('23px')
            ->styleBorderBottom()
            , '10%'
        )
        ->addElementColumn((new Element())
            ->setContent($this->FieldValue['BreakTime'].' Uhr &nbsp;&nbsp;&nbsp;')
            ->stylePaddingTop('4px')
            ->styleTextSize('12px')
            ->styleAlignRight()
            ->styleHeight('23px')
            ->styleBorderBottom()
            , '10%'
        );
    }

    public function getRevisitSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>20</b> hat die Versicherte Person den Besuch <br/> der Einrichtung wieder aufgenommen?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleHeight('27px')
                ->styleBorderBottom()
                , '35%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['ReturnNo'])
                ->stylePaddingTop()
                ->styleHeight('25px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Nein')
                ->styleTextSize('12px')
                ->stylePaddingTop('6px')
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '7%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['ReturnYes'])
                ->stylePaddingTop()
                ->styleHeight('25px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja, am')
                ->styleTextSize('12px')
                ->stylePaddingTop('6px')
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '6%'
            )

            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['ReturnDate'])
                ->styleTextSize('12px')
                ->stylePaddingTop('6px')
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '22%'
            )->addElementColumn((new Element())
                ->setContent('(TT.MM.JJJJ)')
                ->styleTextSize('12px')
                ->stylePaddingTop('6px')
                ->styleHeight('21px')
                ->styleBorderBottom()
                , '22%'
            );
    }

    public function getNoticSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>21</b> Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '70%'
            )
            ->addElementColumn((new Element())
                ->setContent('War diese Person Augenzeugin/Augenzeuge des Unfalls?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '30%'
            );
    }

    public function getNoticDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['WitnessInfo'])
                ->stylePaddingLeft('5px')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '70%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['EyeWitnessNo'])
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Nein')
                ->styleTextSize('12px')
                ->styleHeight('16px')
                ->stylePaddingTop('4px')
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '11%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['EyeWitnessYes'])
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('12px')
                ->styleHeight('16px')
                ->stylePaddingTop('4px')
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '11%'
            );
    }

    public function getInitialTreatmentSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>22</b> Erstbehandlung: <br/>
                                Name und Anschrift der Ärztin/des Arztes oder des Krankenhauses')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                ->styleHeight('29px')
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>23</b> Beginn und Ende des Besuchs der Einrichtung')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
            );
    }

    public function getInitialTreatmentDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['DoctorAddress'])
                ->stylePaddingLeft('5px')
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '60%'
            )
            ->addElementColumn((new Element())
                ->setContent('Beginn')
                ->stylePaddingLeft('5px')
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleBorderBottom()
                , '6%'
            )
            ->addElementColumn((new Element())
                ->setContent('13:50')
                ->setContent($this->FieldValue['LocalStartTime'])
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleAlignRight()
                ->styleBorderBottom()
                , '6%'
            )
            ->addElementColumn((new Element())
                ->setContent('Uhr')
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleAlignCenter()
                ->styleBorderRight()
                ->styleBorderBottom()
                , '8%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ende')
                ->stylePaddingLeft('5px')
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleBorderBottom()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['LocalEndTime'])
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleAlignRight()
                ->styleBorderBottom()
                , '7%'
            )
            ->addElementColumn((new Element())
                ->setContent('Uhr')
                ->styleHeight()
                ->styleTextSize('11px')
                ->styleAlignCenter()
                ->styleBorderBottom()
                , '8%'
            );
    }

    public function getDateDataSection()
    {

        return (new Section())
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
            );
    }

    public function getDateSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>24</b> Datum')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '20%'
            )
            ->addElementColumn((new Element())
                ->setContent('Leiter/-in (Beauftragte/-r) der Einrichtung')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '40%'
            )
            ->addElementColumn((new Element())
                ->setContent('Telefon-Nr. für Rückfragen')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '40%'
            );
    }

}