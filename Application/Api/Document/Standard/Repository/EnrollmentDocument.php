<?php
namespace SPHERE\Application\Api\Document\Standard\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;

/**
 * Class EnrollmentDocument
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository
 */
class EnrollmentDocument extends AbstractDocument
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

        $isBerlin = false;
        // Berlin
        if(Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)){
            $isBerlin = true;
        }
//        //getPerson
        $this->FieldValue['IsNoStudent'] = (isset($DataPost['IsStudent']) && $DataPost['IsStudent'] == 'false' ? true : false);
//        $this->FieldValue['PersonId'] = $PersonId = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
//        $this->FieldValue['SchoolId'] = $CompanyId = (isset($DataPost['SchoolId']) && $DataPost['SchoolId'] != '' ? $DataPost['SchoolId'] : false);
        // school
        $this->FieldValue['School'] = (isset($DataPost['School']) && $DataPost['School'] != '' ? $DataPost['School'] : '&nbsp;');
        $this->FieldValue['SchoolExtended'] = (isset($DataPost['SchoolExtended']) && $DataPost['SchoolExtended'] != '' ? $DataPost['SchoolExtended'] : '&nbsp;');
        $this->FieldValue['SchoolAddressDistrict'] = (isset($DataPost['SchoolAddressDistrict']) && $DataPost['SchoolAddressDistrict'] != '' ? $DataPost['SchoolAddressDistrict'] : '&nbsp;');
        $this->FieldValue['SchoolAddressStreet'] = (isset($DataPost['SchoolAddressStreet']) && $DataPost['SchoolAddressStreet'] != '' ? $DataPost['SchoolAddressStreet'] : '&nbsp;');
        $this->FieldValue['SchoolAddressCity'] = (isset($DataPost['SchoolAddressCity']) && $DataPost['SchoolAddressCity'] != '' ? $DataPost['SchoolAddressCity'] : '&nbsp;');
        // school Berlin extended
        if($isBerlin){
            $this->FieldValue['CompanySchoolLeader'] = (isset($DataPost['CompanySchoolLeader']) && $DataPost['CompanySchoolLeader'] != '' ? $DataPost['CompanySchoolLeader'] : '&nbsp;');
            $this->FieldValue['CompanySecretary'] = (isset($DataPost['CompanySecretary']) && $DataPost['CompanySecretary'] != '' ? $DataPost['CompanySecretary'] : '&nbsp;');
            $this->FieldValue['CompanyPhone'] = (isset($DataPost['CompanyPhone']) && $DataPost['CompanyPhone'] != '' ? $DataPost['CompanyPhone'] : '&nbsp;');
            $this->FieldValue['CompanyFax'] = (isset($DataPost['CompanyFax']) && $DataPost['CompanyFax'] != '' ? $DataPost['CompanyFax'] : '&nbsp;');
            $this->FieldValue['CompanyMail'] = (isset($DataPost['CompanyMail']) && $DataPost['CompanyMail'] != '' ? $DataPost['CompanyMail'] : '&nbsp;');
            $this->FieldValue['CompanyWeb'] = (isset($DataPost['CompanyWeb']) && $DataPost['CompanyWeb'] != '' ? $DataPost['CompanyWeb'] : '&nbsp;');
        }
        // student
        $this->FieldValue['FirstLastName'] = (isset($DataPost['FirstLastName']) && $DataPost['FirstLastName'] != '' ? $DataPost['FirstLastName'] : '&nbsp;');
        $this->FieldValue['Gender'] = (isset($DataPost['Gender']) && $DataPost['Gender'] != '' ? $DataPost['Gender'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['AddressExtra'] = (isset($DataPost['AddressExtra']) && $DataPost['AddressExtra'] != '' ? $DataPost['AddressExtra'] : '&nbsp;');
        $this->FieldValue['AddressDistrict'] = (isset($DataPost['AddressDistrict']) && $DataPost['AddressDistrict'] != '' ? $DataPost['AddressDistrict'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressPLZ'] = (isset($DataPost['AddressPLZ']) && $DataPost['AddressPLZ'] != '' ? $DataPost['AddressPLZ'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        $this->FieldValue['Birthplace'] = (isset($DataPost['Birthplace']) && $DataPost['Birthplace'] != '' ? $DataPost['Birthplace'] : '&nbsp;');

        // set position for address
        if($this->FieldValue['AddressDistrict'] != '&nbsp;' && $this->FieldValue['AddressExtra'] != '&nbsp;'){
            $this->FieldValue['AddressFirstLine'] = $this->FieldValue['AddressExtra'];
            $this->FieldValue['AddressSecondLine'] = $this->FieldValue['AddressDistrict'];
            $this->FieldValue['AddressThirdLine'] =  $this->FieldValue['AddressStreet'];
            $this->FieldValue['AddressFourthLine'] = $this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'];
        } elseif($this->FieldValue['AddressDistrict'] == '&nbsp;' && $this->FieldValue['AddressExtra'] != '&nbsp;') {
            $this->FieldValue['AddressFirstLine'] = $this->FieldValue['AddressExtra'];
            $this->FieldValue['AddressSecondLine'] = $this->FieldValue['AddressStreet'];
            $this->FieldValue['AddressThirdLine'] = $this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'];
            $this->FieldValue['AddressFourthLine'] = '&nbsp;';
        } elseif($this->FieldValue['AddressDistrict'] != '&nbsp;' && $this->FieldValue['AddressExtra'] == '&nbsp;'){
            $this->FieldValue['AddressFirstLine'] = $this->FieldValue['AddressDistrict'];
            $this->FieldValue['AddressSecondLine'] = $this->FieldValue['AddressStreet'];
            $this->FieldValue['AddressThirdLine'] = $this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'];
            $this->FieldValue['AddressFourthLine'] = '&nbsp;';
        } else {
            $this->FieldValue['AddressFirstLine'] = $this->FieldValue['AddressStreet'];
            $this->FieldValue['AddressSecondLine'] = $this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'];
            $this->FieldValue['AddressThirdLine'] = '&nbsp;';
            $this->FieldValue['AddressFourthLine'] = '&nbsp;';
        }

        $this->FieldValue['Division'] = (isset($DataPost['Division']) && $DataPost['Division'] != '' ? $DataPost['Division'] : '&nbsp;');
        $this->FieldValue['ArriveDate'] = (isset($DataPost['ArriveDate']) && $DataPost['ArriveDate'] != '' ? $DataPost['ArriveDate'] : '&nbsp;');
        $this->FieldValue['LeaveDate'] = (isset($DataPost['LeaveDate']) && $DataPost['LeaveDate'] != '' ? $DataPost['LeaveDate'] : '&nbsp;');
        // common

        $this->FieldValue['Male'] = 'false';
        $this->FieldValue['Female'] = 'false';
        if (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == TblCommonGender::VALUE_MALE_STRING) {
            $this->FieldValue['Male'] = 'true';
        } elseif (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == TblCommonGender::VALUE_FEMALE_STRING) {
            $this->FieldValue['Female'] = 'true';
        }
        // last line
        $this->FieldValue['Place'] = (isset($DataPost['Place']) && $DataPost['Place'] != '' ? $DataPost['Place'].', ' : '&nbsp;');
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schulbescheinigung';
    }

    /**
     * @param array $pageList
     * @param string $part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $part = '0'): Frame
    {

        if(Consumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)){
            // Berlin
            return $this->getDefaultBerlinPage();
        } else {
            // Sachsen
            if($this->FieldValue['IsNoStudent']){
                // Ehemalige
                return $this->getDefaultArchivPage();
            } else {
                // Aktuell Schüler
                return $this->getDefaultPage();
            }
        }
    }

    private function getDefaultBerlinPage()
    {
        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'7%'
                        )
                        ->addSliceColumn((new Slice())
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['School']
                                            .($this->FieldValue['SchoolExtended'] != '&nbsp;' ? ' '.$this->FieldValue['SchoolExtended'] : '')
                                            .($this->FieldValue['SchoolAddressDistrict'] != '&nbsp;' ? ' '.$this->FieldValue['SchoolAddressDistrict'] : '')
                                            .($this->FieldValue['SchoolAddressStreet'] != '&nbsp;' ? ' | '.$this->FieldValue['SchoolAddressStreet'] : '')
                                            .($this->FieldValue['SchoolAddressCity'] != '&nbsp;' ? ' | '.$this->FieldValue['SchoolAddressCity'] : ''))
                                        ->styleMarginTop('127px')
                                        ->styleHeight('310px')
                                        ->styleTextSize('11px')
                                    )
                                , '70%')
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn($this->getPictureEnrollmentDocument()
//                                            ->styleAlignRight()
                                            ->styleHeight('127px')
                                        )
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['School'])
                                        ->styleTextSize('11px')
                                        ->styleTextBold()
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['SchoolAddressStreet'])
                                        ->styleTextSize('11px')
                                        ->stylePaddingTop('8px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['SchoolAddressCity'])
                                        ->styleTextSize('11px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent('Schulleiter')
                                        ->styleTextSize('11px')
                                        ->stylePaddingTop('8px')
                                        ->styleTextBold()
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['CompanySchoolLeader'])
                                        ->styleTextSize('11px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent('Sekretariat')
                                        ->styleTextSize('11px')
                                        ->stylePaddingTop('8px')
                                        ->styleTextBold()
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['CompanySecretary'])
                                        ->styleTextSize('11px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent('Telefon &nbsp;&nbsp;&nbsp;'.$this->FieldValue['CompanyPhone'])
                                        ->styleTextSize('11px')
                                        ->stylePaddingTop('8px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent('Fax &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->FieldValue['CompanyFax'])
                                        ->styleTextSize('11px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['CompanyMail'])
                                        ->styleTextSize('11px')
                                        ->stylePaddingTop('8px')
                                    )
                                    ->addElement((new Element())
                                        ->setContent($this->FieldValue['CompanyWeb'])
                                        ->styleTextSize('11px')
                                    )
                                , '30%')
                            )

                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulbescheinigung')
                                    ->styleTextSize('18px')
                                    ->styleTextBold()
                                )
                            )
                            ->addElement((new Element())
                                ->setContent('
                                    {% if '.$this->FieldValue['Female'].' == "true" %} Die Schülerin
                                    {% elseif '.$this->FieldValue['Male'].' == "true" %} Der Schüler
                                    {% else %} Der Schüler/Die Schülerin
                                    {% endif %}')
                                ->stylePaddingTop('78px')
                                ->styleTextSize('17px')
                            )
                            ->addElement((new Element())
                                ->setContent($this->FieldValue['FirstLastName'])
                                ->stylePaddingTop('50px')
                                ->styleTextSize('17px')
                                ->styleTextBold()
                            )
                            ->addElement((new Element())
                                ->setContent('geboren am '.$this->FieldValue['Birthday'].' besucht zur Zeit die Klasse '.$this->FieldValue['Division'].' und
                                wird voraussichtlich')
                                ->stylePaddingTop('47px')
                                ->styleTextSize('17px')
                            )
                            ->addElement((new Element())
                                ->setContent('noch bis zum '.$this->FieldValue['LeaveDate'].' diese Schule besuchen')
                                ->stylePaddingTop('12px')
                                ->styleTextSize('17px')
                            )
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent($this->FieldValue['SchoolAddressCity'].', den '.$this->FieldValue['Date'])
                        ->stylePaddingTop('92px')
                        ->styleAlignCenter()
                        ->styleTextSize('12px')
                    )
                    ->addElement((new Element())
                        ->setContent('Schulleiter')
                        ->stylePaddingTop('67px')
                        ->styleAlignCenter()
                        ->styleTextSize('12px')
                    )
                )
                // save if Slice is too long for Page
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->stylePaddingTop('60px')
                    )
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;')
                        , '7%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                Vorsitzender des Kuratoriums:<br/>
                                Jost Arnsperger<br/>
                                Vorstand: Frank Olie (Vorsitzender), Chstina Lier')
                            ->styleTextSize('9px')
                            ->styleHeight('10px')
                            , '31%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                Bankverbindung:<br/>
                                IBAN: DE26 5206 0410 1503 9073 25<br/>
                                BIC: GENODE1EK1')
                            ->styleTextSize('9px')
                            ->styleHeight('10px')
                            , '31%')
                        ->addElementColumn((new Element())
                            ->setContent('
                                Eine Stiftung der Evangelischen Kirche<br/>
                                Berlin-Brandenburg-schlesische Oberlausitz')
                            ->styleTextSize('9px')
                            ->styleHeight('10px')
                            , '31%')
                    )
                )
            )
        );
    }

    private function getDefaultPage()
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
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('&nbsp;')
                                            ->styleHeight('25px')
                                        )
                                    )
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('Schule')
                                            ->styleHeight('15px')
                                            ->styleTextSize('9pt')
                                        )
                                    )
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['School']
                                                .($this->FieldValue['SchoolExtended'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolExtended'] : '')
                                                .($this->FieldValue['SchoolAddressDistrict'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressDistrict'] : '')
                                                .($this->FieldValue['SchoolAddressStreet'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressStreet'] : '')
                                                .($this->FieldValue['SchoolAddressCity'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressCity'] : '')
                                            )
                                            ->styleHeight('140px')
                                        )
                                    ), '60%'
                                )
                                ->addElementColumn($this->getPictureEnrollmentDocument()
                                    ->styleAlignRight()
                                    ,'40%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulbescheinigung')
                                    ->styleTextSize('25px')
                                    ->styleTextBold()
                                    ->styleTextItalic()
                                )
                            )
                            ->addSection($this->getSection('
                                {% if '.$this->FieldValue['Female'].' == "true" %} Die Schülerin
                                {% elseif '.$this->FieldValue['Male'].' == "true" %} Der Schüler
                                {% else %} Der Schüler/Die Schülerin
                                {% endif %}', $this->FieldValue['FirstLastName'], '50px'))
                            ->addSection($this->getSection('geboren am', $this->FieldValue['Birthday']))
                            ->addSection($this->getSection('geboren in', $this->FieldValue['Birthplace']))
                            ->addSection($this->getSection('wohnhaft', $this->FieldValue['AddressFirstLine']))
                            ->addSection($this->getSection('&nbsp;', $this->FieldValue['AddressSecondLine']))
                            ->addSection($this->getSection('&nbsp;', $this->FieldValue['AddressThirdLine']))
                            ->addSection($this->getSection('&nbsp;', $this->FieldValue['AddressFourthLine']))
                            ->addSection($this->getSection('besucht zurzeit die Klasse', $this->FieldValue['Division']))
                            ->addSection($this->getSection('
                                {% if '.$this->FieldValue['Female'].' == "true" %} Sie
                                {% elseif '.$this->FieldValue['Male'].' == "true" %} Er
                                {% else %} Er/Sie
                                {% endif %}
                                besucht unsere Schule seit', $this->FieldValue['ArriveDate']))
                            ->addSection($this->getSection('und wird voraussichtlich bis zum', $this->FieldValue['LeaveDate']))
                            ->addElement((new Element())
                                ->setContent('
                                    {% if '.$this->FieldValue['Female'].' == "true" %} Schülerin
                                    {% elseif '.$this->FieldValue['Male'].' == "true" %} Schüler
                                    {% else %} Schüler/Schülerin
                                    {% endif %} unserer Schule sein.')
                                ->stylePaddingTop('30px')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Place'].$this->FieldValue['Date'])
                                    ->stylePaddingTop('100px')
                                    ->styleBorderBottom()
                                    , '45%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    ->stylePaddingTop('100px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    ->stylePaddingTop('100px')
                                    ->styleBorderBottom()
                                    , '35%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                Ort, Datum
                            ')
                                    ->styleTextSize('12px')
                                    , '45%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      Schulstempel
                             ')
                                    ->stylePaddingTop('0px')
                                    ->styleMarginTop('0px')
                                    ->styleTextSize('12px')
                                    , '35%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                &nbsp;
                            ')
                                    ->stylePaddingTop('30px')
                                    , '65%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '35%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    , '65%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      Schulleiter/in
                             ')
                                    ->stylePaddingTop('0px')
                                    ->styleMarginTop('0px')
                                    ->styleTextSize('12px')
                                    , '35%')
                            ),'90%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'5%'
                        )
                    )
                )
            )
        );
    }

    private function getDefaultArchivPage()
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
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('&nbsp;')
                                            ->styleHeight('25px')
                                        )
                                    )
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('Schule')
                                            ->styleHeight('15px')
                                            ->styleTextSize('9pt')
                                        )
                                    )
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['School']
                                                .($this->FieldValue['SchoolExtended'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolExtended'] : '')
                                                .($this->FieldValue['SchoolAddressDistrict'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressDistrict'] : '')
                                                .($this->FieldValue['SchoolAddressStreet'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressStreet'] : '')
                                                .($this->FieldValue['SchoolAddressCity'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressCity'] : '')
                                            )
                                            ->styleHeight('140px')
                                        )
                                    ), '60%'
                                )
                                ->addElementColumn($this->getPictureEnrollmentDocument()
                                    ->styleAlignRight()
                                    ,'40%'
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulbescheinigung')
                                    ->styleTextSize('25px')
                                    ->styleTextBold()
                                    ->styleTextItalic()
                                )
                            )
                            ->addSection($this->getSection('
                                {% if '.$this->FieldValue['Female'].' == "true" %} Die Schülerin
                                {% elseif '.$this->FieldValue['Male'].' == "true" %} Der Schüler
                                {% else %} Der Schüler/Die Schülerin
                                {% endif %}', $this->FieldValue['FirstLastName'], '50px'))
                            ->addSection($this->getSection('geboren am', $this->FieldValue['Birthday']))
                            ->addSection($this->getSection('geboren in', $this->FieldValue['Birthplace']))
                            ->addSection($this->getSection('wohnhaft', $this->FieldValue['AddressFirstLine']))
                            ->addSection($this->getSection('&nbsp;', $this->FieldValue['AddressSecondLine']))
                            ->addSection($this->getSection('&nbsp;', $this->FieldValue['AddressThirdLine']))
                            ->addSection($this->getSection('&nbsp;', $this->FieldValue['AddressFourthLine']))
                            ->addSection($this->getSection('war vom', $this->FieldValue['ArriveDate']))
                            ->addSection($this->getSection('bis zum', $this->FieldValue['LeaveDate']))
                            ->addElement((new Element())
                                ->setContent('
                                    {% if '.$this->FieldValue['Female'].' == "true" %} Schülerin
                                    {% elseif '.$this->FieldValue['Male'].' == "true" %} Schüler
                                    {% else %} Schüler/Schülerin
                                    {% endif %} unserer Schule.')
                                ->stylePaddingTop('30px')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Place'].$this->FieldValue['Date'])
                                    ->stylePaddingTop('100px')
                                    ->styleBorderBottom()
                                    , '45%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->stylePaddingTop('100px')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->stylePaddingTop('100px')
                                    ->styleBorderBottom()
                                    , '35%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Ort, Datum')
                                    ->styleTextSize('12px')
                                    , '45%')
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    , '20%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      Schulstempel
                             ')
                                    ->stylePaddingTop('0px')
                                    ->styleMarginTop('0px')
                                    ->styleTextSize('12px')
                                    , '35%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                &nbsp;
                            ')
                                    ->stylePaddingTop('30px')
                                    , '65%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '35%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      &nbsp;
                             ')
                                    , '65%')
                                ->addElementColumn((new Element())
                                    ->setContent('
                                      Schulleiter/in
                             ')
                                    ->stylePaddingTop('0px')
                                    ->styleMarginTop('0px')
                                    ->styleTextSize('12px')
                                    , '35%')
                            ),'90%'
                        )
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'),'5%'
                        )
                    )
                )
            )
        );
    }

    /**
     * @param string $Title
     * @param string $Value
     * @param string $PaddingTop
     * @return Section
     */
    private function getSection(string $Title, string $Value, string $PaddingTop = '30px'):Section
    {

        return (new Section())
            ->addElementColumn((new Element())
                ->setContent($Title)
                ->stylePaddingTop($PaddingTop)
                , '35%')
            ->addElementColumn((new Element())
                ->setContent($Value)
                ->stylePaddingTop($PaddingTop)
                ->styleBorderBottom()
                , '65%');
    }
}