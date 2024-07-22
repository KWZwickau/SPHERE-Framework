<?php
namespace SPHERE\Application\Api\Document\Standard\Repository\StaffAccidentReport;

use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Common\Frontend\Text\Repository\Code;
use SPHERE\System\Extension\Extension;

/**
 * Class Style
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\StaffAccidentReport
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
        // relation
        $this->FieldValue['PersonOwner'] = (isset($DataPost['PersonOwner']) && $DataPost['PersonOwner'] != '' ? 'X' : '');
        $this->FieldValue['PersonShareholder'] = (isset($DataPost['PersonShareholder']) && $DataPost['PersonShareholder'] != '' ? 'X' : '');
        $this->FieldValue['PersonMarried'] = (isset($DataPost['PersonMarried']) && $DataPost['PersonMarried'] != '' ? 'X' : '');
        $this->FieldValue['PersonTogether'] = (isset($DataPost['PersonTogether']) && $DataPost['PersonTogether'] != '' ? 'X' : '');
        $this->FieldValue['PersonRelated'] = (isset($DataPost['PersonRelated']) && $DataPost['PersonRelated'] != '' ? 'X' : '');
        $this->FieldValue['PersonCloser'] = '';
        if($this->FieldValue['PersonMarried'] || $this->FieldValue['PersonRelated'] || $this->FieldValue['PersonTogether']){
            $this->FieldValue['PersonCloser'] = 'X';
        }
        // accident
        $this->FieldValue['DeathAccidentYes'] = (isset($DataPost['DeathAccidentYes']) && $DataPost['DeathAccidentYes'] != '' ? 'X' : '');
        $this->FieldValue['DeathAccidentNo'] = (isset($DataPost['DeathAccidentNo']) && $DataPost['DeathAccidentNo'] != '' ? 'X' : '');
        $this->FieldValue['AccidentDate'] = (isset($DataPost['AccidentDate']) && $DataPost['AccidentDate'] != '' ? $DataPost['AccidentDate'] : '&nbsp;');
        $this->FieldValue['AccidentTime'] = (isset($DataPost['AccidentTime']) && $DataPost['AccidentTime'] != '' ? $DataPost['AccidentTime'] : '&nbsp;');
        $this->FieldValue['PersonPhone'] = (isset($DataPost['PersonPhone']) && $DataPost['PersonPhone'] != '' ? $DataPost['PersonPhone'] : '&nbsp;');
        $this->FieldValue['AccidentPlace'] = (isset($DataPost['AccidentPlace']) && $DataPost['AccidentPlace'] != '' ? $DataPost['AccidentPlace'] : '&nbsp;');

        $this->FieldValue['HomeOfficeNo'] = (isset($DataPost['HomeOfficeNo']) && $DataPost['HomeOfficeNo'] != '' ? 'X' : '');
        $this->FieldValue['HomeOfficeYes'] = (isset($DataPost['HomeOfficeYes']) && $DataPost['HomeOfficeYes'] != '' ? 'X' : '');
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
        $this->FieldValue['Doctor'] = (isset($DataPost['Doctor']) && $DataPost['Doctor'] != '' ? $DataPost['Doctor'] : '&nbsp;');
        $this->FieldValue['DoctorAddress'] = (isset($DataPost['DoctorAddress']) && $DataPost['DoctorAddress'] != '' ? $DataPost['DoctorAddress'] : '&nbsp;');
        // time in school
        $this->FieldValue['LocalStartTime'] = (isset($DataPost['LocalStartTime']) && $DataPost['LocalStartTime'] != '' ? $DataPost['LocalStartTime'] : '&nbsp;');
        $this->FieldValue['LocalEndTime'] = (isset($DataPost['LocalEndTime']) && $DataPost['LocalEndTime'] != '' ? $DataPost['LocalEndTime'] : '&nbsp;');
        // worker
        $this->FieldValue['TemporaryWorkYes'] = (isset($DataPost['TemporaryWorkYes']) && $DataPost['TemporaryWorkYes'] != '' ? 'X' : '');
        $this->FieldValue['TemporaryWorkNo'] = (isset($DataPost['TemporaryWorkNo']) && $DataPost['TemporaryWorkNo'] != '' ? 'X' : '');
        $this->FieldValue['ApprenticeYes'] = (isset($DataPost['ApprenticeYes']) && $DataPost['ApprenticeYes'] != '' ? 'X' : '');
        $this->FieldValue['ApprenticeNo'] = (isset($DataPost['ApprenticeNo']) && $DataPost['ApprenticeNo'] != '' ? 'X' : '');

        $this->FieldValue['ContinuePayment'] = (isset($DataPost['ContinuePayment']) && $DataPost['ContinuePayment'] != '' ? $DataPost['ContinuePayment'] : '&nbsp;');

        $this->FieldValue['WorkAtAccident'] = (isset($DataPost['WorkAtAccident']) && $DataPost['WorkAtAccident'] != '' ? $DataPost['WorkAtAccident'] : '&nbsp;');
        $this->FieldValue['LocationSince'] = (isset($DataPost['LocationSince']) && $DataPost['LocationSince'] != '' ? $DataPost['LocationSince'] : '&nbsp;');
        $this->FieldValue['WorkArea'] = (isset($DataPost['WorkArea']) && $DataPost['WorkArea'] != '' ? $DataPost['WorkArea'] : '&nbsp;');
        $this->FieldValue['HealthInsurance'] = (isset($DataPost['HealthInsurance']) && $DataPost['HealthInsurance'] != '' ? $DataPost['HealthInsurance'] : '&nbsp;');
        $this->FieldValue['Council'] = (isset($DataPost['Council']) && $DataPost['Council'] != '' ? $DataPost['Council'] : '&nbsp;');
        // last line
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');
        $this->FieldValue['LocalLeader'] = (isset($DataPost['LocalLeader']) && $DataPost['LocalLeader'] != '' ? $DataPost['LocalLeader'] : '&nbsp;');
        $this->FieldValue['Recall'] = (isset($DataPost['Recall']) && $DataPost['Recall'] != '' ? $DataPost['Recall'] : '&nbsp;');

        return $this;
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
                ->setContent('<b>3</b> Empfänger')
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
                ->styleHeight('40px')
            )
            , '60%'
        )
        ->addSliceColumn((new Slice())
            ->addElement((new Element())
                ->setContent('UNFALLANZEIGE')
                ->styleTextSize('24px')
                ->styleTextBold()
                ->stylePaddingBottom('15px')
            )
            ->addElement((new Element())
                ->setContent('<b>2</b> Unternehmensnummer des Unfallversicherungsträgers')
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
                ->setContent('<b>4</b> Name, Vorname der versicherten Person')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '72%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>5</b> Geburtsdatum')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '28%'
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
                , '72%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Birthday'])
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '28%'
            );
    }

    public function getAddressSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>6</b> Straße, Hausnummer')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '55%'
            )
            ->addElementColumn((new Element())
                ->setContent('Postleitzahl')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '17%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ort')
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                , '28%'
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
                , '55%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressPLZ'])
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '17%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressCity'])
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '28%'
            );
    }

    public function getGenderSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>7</b> Geschlecht')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '55%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>8</b> Staatsangehörigkeit')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '17%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>9</b> Leiharbeitnehmer/in')
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                , '28%'
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
                ->styleHeight('18px')
                ->stylePaddingTop('4px')
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
                ->styleHeight('18px')
                ->stylePaddingTop('4px')
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
                ->styleHeight('18px')
                ->stylePaddingTop('4px')
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
                ->styleHeight('18px')
                ->stylePaddingTop('4px')
                ->styleBorderBottom()
                ->styleBorderRight()
                , '12%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Nationality'])
                ->stylePaddingLeft('5px')
                ->styleHeight('20px')
                ->stylePaddingTop()
                ->styleBorderRight()
                ->styleBorderBottom()
                , '17%'
            )
            ->addSliceColumn(
                $this->setCheckBox((isset($this->FieldValue['TemporaryWorkNo']) && $this->FieldValue['TemporaryWorkNo'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('22px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Nein')
                ->styleTextSize('12px')
                ->styleHeight('18px')
                ->stylePaddingTop('4px')
                ->styleBorderBottom()
                , '10%'
            )
            ->addSliceColumn(
                $this->setCheckBox((isset($this->FieldValue['TemporaryWorkYes']) && $this->FieldValue['TemporaryWorkYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('22px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('12px')
                ->styleHeight('18px')
                ->stylePaddingTop('4px')
                ->styleBorderBottom()
                , '10%'
            );
    }

    public function getEducationDataSection()
    {
        return (new Section())
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('<b>10</b> Auszubildende/-r')
                    ->styleTextSize('11px')
                    ->stylePaddingTop('4px')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    ->styleHeight('20px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '8%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['ApprenticeNo'] ? 'X' : ''))
                        , '17%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Nein')
                        ->styleTextSize('12px')
                        ->stylePaddingTop('4px')
                        , '29%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['ApprenticeYes'] ? 'X' : ''))
                        , '17%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Ja')
                        ->styleTextSize('12px')
                        ->stylePaddingTop('4px')
                        ->styleBorderRight()
                        ->styleHeight('38px')
                        , '29%'
                    )
                )
            , '24%')
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('<b>11</b> Die versicherte Person ist')
                    ->styleTextSize('11px')
                    ->stylePaddingTop('4px')
                    ->stylePaddingLeft('5px')
                    ->styleHeight('20px')
                )
            , '22%')
            ->addSliceColumn((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonOwner'] ? 'X' : ''))
                            ->styleHeight('15px')
                        , '18%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Unternehmer/-in')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('4px')
                        ->styleHeight('15px')
                        , '82%'
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonShareholder'] ? 'X' : ''))
                        , '18%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Gesellschafter/-in </br>Geschäftsführer/-in ')
                        ->styleTextSize('11px')
                        , '82%'
                    )
                )
            , '22%')
            ->addSliceColumn((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonCloser'] ? 'X' : ''))
                            ->styleHeight('15px')
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('mit dem/der Unternehmer/-in')
                        ->stylePaddingTop('4px')
                        ->styleTextSize('11px')
                        ->styleHeight('15px')
                        , '88%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonMarried'] ? 'X' : ''))
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('verheiratet/in eingetragener </br>Lebenspartnerschaft lebend')
                        ->styleTextSize('11px')
                        , '83%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonRelated'] ? 'X' : ''))
                            ->styleHeight('20px')
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('verwandt')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('4px')
                        , '83%'
                    )
                )
            , '32%');
    }

    public function getEducationDataSectionBE()
    {
        return (new Section())
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('<b>10</b> Auszubildende/-r')
                    ->styleTextSize('11px')
                    ->stylePaddingTop('4px')
                    ->stylePaddingLeft('5px')
                    ->styleBorderRight()
                    ->styleHeight('20px')
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        , '8%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['ApprenticeNo'] ? 'X' : ''))
                        , '17%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Nein')
                        ->styleTextSize('12px')
                        ->stylePaddingTop('4px')
                        , '29%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['ApprenticeYes'] ? 'X' : ''))
                        , '17%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Ja')
                        ->styleTextSize('12px')
                        ->stylePaddingTop('4px')
                        ->styleBorderRight()
                        ->styleHeight('56px')
                        , '29%'
                    )
                )
            , '24%')
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('<b>11</b> Die versicherte Person ist')
                    ->styleTextSize('11px')
                    ->stylePaddingTop('4px')
                    ->stylePaddingLeft('5px')
                    ->styleHeight('20px')
                )
            , '22%')
            ->addSliceColumn((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonOwner'] ? 'X' : ''))
                            ->styleHeight('15px')
                        , '18%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Unternehmer/-in')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('4px')
                        ->styleHeight('15px')
                        , '82%'
                    )
                )
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonShareholder'] ? 'X' : ''))
                        , '18%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('Gesellschafter/-in </br>Geschäftsführer/-in ')
                        ->styleTextSize('11px')
                        , '82%'
                    )
                )
            , '22%')
            ->addSliceColumn((new Slice())
                ->addSection((new Section())
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonCloser'] ? 'X' : ''))
                            ->styleHeight('15px')
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('mit dem/der Unternehmer/-in')
                        ->stylePaddingTop('4px')
                        ->styleTextSize('11px')
                        ->styleHeight('15px')
                        , '88%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonMarried'] ? 'X' : ''))
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('verheiratet')
                        ->styleTextSize('11px')
                        , '83%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonTogether'] ? 'X' : ''))
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('in eingetragener Lebenspartnerschaft lebend')
                        ->styleTextSize('11px')
                        , '83%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '5%'
                    )
                    ->addSliceColumn(
                        $this->setCheckBox(($this->FieldValue['PersonRelated'] ? 'X' : ''))
                            ->styleHeight('20px')
                        , '12%'
                    )
                    ->addElementColumn((new Element())
                        ->setContent('verwandt')
                        ->styleTextSize('11px')
                        ->stylePaddingTop('4px')
                        , '83%'
                    )
                )
            , '32%');
    }

    public function getInsuranceSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>12</b> Anspruch auf Entgeltfortzahlung')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderTop()
                ->styleBorderRight()
                , '30%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>13</b>  Krankenkasse (Name, PLZ, Ort, bei Familienversicherung Name des Mitglieds)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderTop()
                , '70%'
            );
    }

    public function getInsuranceDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('besteht für')
                ->styleTextSize('11px')
                ->stylePaddingLeft('7px')
                ->styleBorderBottom()
                ->styleHeight('15px')
                , '9%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['ContinuePayment'])
                ->styleTextSize('11px')
                ->styleBorderBottom()
                ->styleHeight('15px')
                , '2%'
            )
            ->addElementColumn((new Element())
                ->setContent('Wochen')
                ->styleTextSize('11px')
                ->stylePaddingLeft('7px')
                ->styleBorderBottom()
                ->styleBorderRight()
                ->styleHeight('15px')
                , '19%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['HealthInsurance'])
                ->stylePaddingLeft('5px')
                ->styleHeight('15px')
                ->styleBorderBottom()
                , '70%'
            );
    }

    public function getDeadlyAccidentSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>14</b> Tödlicher Unfall?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '24%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>15</b> Unfallzeitpunkt (TT.MM.JJJJ/hh:mm)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '76%'
            );
    }

    public function getDeadlyAccidentSectionTH()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>14</b> Tödlicher Unfall?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '24%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>15</b> Unfallzeitpunkt (TT.MM.JJJJ/hh:mm)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '48%'
            )
            ->addElementColumn((new Element())
                ->setContent('Telefonnummer der versicherten Person')
                ->styleTextSize('10px')
                ->stylePaddingLeft('5px')
                , '28%'
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
                ->setContent('Nein')
                ->styleTextSize('12px')
                ->styleHeight('18px')
                ->stylePaddingTop('3px')
                ->styleBorderBottom()
                , '7%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('12px')
                ->styleHeight('18px')
                ->stylePaddingTop('3px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '7%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentDate'])
                ->stylePaddingLeft('5px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '21%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentTime'].' Uhr')
                ->stylePaddingLeft('5px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleHeight('19px')
                , '55%'
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
                ->setContent('Nein')
                ->styleTextSize('12px')
                ->styleHeight('18px')
                ->stylePaddingTop('3px')
                ->styleBorderBottom()
                , '7%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['DeathAccidentYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('12px')
                ->styleHeight('18px')
                ->stylePaddingTop('3px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '7%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentDate'])
                ->stylePaddingLeft('5px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                , '21%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentTime'].' Uhr')
                ->stylePaddingLeft('5px')
                ->stylePaddingTop()
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleHeight('19px')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['PersonPhone'])
                ->stylePaddingLeft('5px')
                ->stylePaddingTop()
                ->styleBorderBottom()
                ->styleHeight('19px')
                , '28%'
            );
    }

    public function getAccidentLocationSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>16</b> Unfallort (genaue Orts- und Straßenangabe mit PLZ)')
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                ->styleBorderRight()
                , '80%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>17</b> Unfall im Homeoffice ')
                ->stylePaddingLeft('5px')
                ->styleTextSize('11px')
                , '20%'
            );
    }

    public function getAccidentLocationDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AccidentPlace'])
                ->stylePaddingLeft('5px')
                ->styleTextSize('12px')
                ->styleHeight('19px')
                ->stylePaddingTop()
                ->styleBorderRight()
                ->styleBorderBottom()
                , '80%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['HomeOfficeNo'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Nein')
                ->styleTextSize('12px')
                ->styleHeight('17px')
                ->stylePaddingTop('4px')
                ->styleBorderBottom()
                , '6%'
            )
            ->addSliceColumn(
                $this->setCheckBox(($this->FieldValue['HomeOfficeYes'] ? 'X' : ''))
                    ->styleBorderBottom()
                    ->styleHeight('21px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('12px')
                ->styleHeight('17px')
                ->stylePaddingTop('4px')
                ->styleBorderBottom()
                , '6%'
            );
    }

    public function getDescriptionSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>18</b> Ausführliche Schilderung des Unfallhergangs (Verlauf, Bezeichnung des Betriebsteils, ggf. Beteiligung von Maschinen, Anlagen, Gefahrstoffen)')
                ->styleTextSize('10px')
                ->stylePaddingLeft('5px')
            );
    }

    public function getDescriptionDataSection($Height = '120px')
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
                ->styleHeight('15px')
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
                ->styleHeight('15px')
                , '22%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionPassive'])
                ->styleHeight('20px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('anderer Personen')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight('15px')
                , '15%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
                , '21%'
            );
    }

    public function getDescriptionViolanceDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('Hat ein Gewaltereignis vorgelegen (körperlicher Übergriff, sexueller Übergriff)?')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight('15px')
                ->stylePaddingLeft('5px')
                , '61%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionViolenceNo'])
                ->styleHeight('20px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Nein')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight('15px')
                , '7%'
            )
            ->addSliceColumn($this->setCheckBox($this->FieldValue['DescriptionViolenceYes'])
                ->styleHeight('20px')
                , '4%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ja')
                ->styleTextSize('11px')
                ->stylePaddingTop('5px')
                ->styleHeight('15px')
                , '15%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
                , '10%'
            );
    }

    public function getHurtSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>19</b> Verletzte Körperteile')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '50%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>20</b> Art der Verletzung')
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

    public function getNoticSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>21</b> Wer hat von dem Unfall zuerst Kenntnis genommen? (Name, Anschrift)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '55%'
            )
            ->addElementColumn((new Element())
                ->setContent('War diese Person Augenzeugin/Augenzeuge des Unfalls?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '45%'
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
                , '55%'
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
                , '6%'
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
                , '6%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '25%'
            );
    }

    public function getInitialTreatmentSectionList()
    {

        return array((new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>22</b> Erstbehandlung:')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '55%')
            ->addElementColumn((new Element())
                ->setContent('<b>23</b> Beginn und Ende der Arbeitszeit der versicherten')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '45%'
            ),
            (new Section())
            ->addElementColumn((new Element())
                ->setContent('Name und Anschrift der Ärztin/des Arztes oder des Krankenhauses')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '55%')
            ->addElementColumn((new Element())
                ->setContent('Person (hh:mm)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '45%')

        );
    }

    public function getInitialTreatmentDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Doctor'].' '.$this->FieldValue['DoctorAddress'])
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '55%')
            ->addElementColumn((new Element())
                ->setContent('Beginn')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['LocalStartTime'])
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleAlignCenter()
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('Uhr')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('Ende')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '5%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['LocalEndTime'])
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleAlignCenter()
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('Uhr')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '5%'
            );
    }

    public function getAccidentJobSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>24</b> Zum Unfallzeitpunkt beschäftigt/tätig als')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                , '55%'
            )
            ->addElementColumn((new Element())
                ->setContent('<b>25</b> Seit wann bei dieser Tätigkeit? (TT.MM.JJJJ)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '45%'
            );
    }

    public function getAccidentJobDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['WorkAtAccident'])
                ->stylePaddingLeft('5px')
                ->styleBorderRight()
                ->styleBorderBottom()
                , '55%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['LocationSince'])
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
                , '45%'
            );
    }

    public function getCompanyPartSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>26</b> In welchem Teil des Unternehmens ist die versicherte Person ständig tätig?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
            );
    }

    public function getCompanyPartDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['WorkArea'])
                ->stylePaddingLeft('5px')
                ->styleBorderBottom()
            );
    }

    public function getBreakDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>27</b> hat der Versicherte die Arbeit eingestellt?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('7px')
                ->styleHeight('20px')
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
                ->stylePaddingTop('7px')
                ->stylePaddingLeft('6px')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('(TT.MM) um')
                ->stylePaddingTop('7px')
                ->styleTextSize('11px')
                ->styleHeight('20px')
                ->styleAlignCenter()
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['BreakTime'])
                ->stylePaddingTop('7px')
                ->stylePaddingLeft('4px')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->styleAlignRight()
                ->styleBorderBottom()
                , '6%'
            )
            ->addElementColumn((new Element())
                ->setContent('Uhr')
                ->stylePaddingTop('7px')
                ->stylePaddingLeft('4px')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '4%'
            );
    }

    public function getRevisitDataSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>28</b> Hat die versicherte Person die Arbeit wieder aufgenommen?')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('7px')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '46%'
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
                , '9%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['ReturnDate'])
                ->stylePaddingTop('7px')
                ->stylePaddingLeft('6px')
                ->styleTextSize('12px')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '10%'
            )
            ->addElementColumn((new Element())
                ->setContent('(TT.MM.JJJJ)')
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('7px')
                ->styleTextSize('11px')
                ->styleHeight('20px')
                ->styleBorderBottom()
                , '20%'
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
                , '13%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['LocalLeader'])
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('22px')
                ->styleHeight('18px')
                ->styleBorderBottom()
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Council'])
                ->stylePaddingLeft('10px')
                ->stylePaddingTop('22px')
                ->styleHeight('18px')
                ->styleBorderBottom()
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Recall'])
                ->stylePaddingLeft('5px')
                ->stylePaddingTop('22px')
                ->styleHeight('18px')
                ->styleBorderBottom()
                , '35%'
            );
    }

    public function getDateSection()
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent('<b>29</b> Datum')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '13%'
            )
            ->addElementColumn((new Element())
                ->setContent('Unternehmer/-in (Bevollmächtigte/-r)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '27%'
            )
            ->addElementColumn((new Element())
                ->setContent('Betriebsrat (Personalrat)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('10px')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent('Telefon-Nr. für Rückfragen (Ansprechpartner)')
                ->styleTextSize('11px')
                ->stylePaddingLeft('5px')
                , '35%'
            );
    }

}