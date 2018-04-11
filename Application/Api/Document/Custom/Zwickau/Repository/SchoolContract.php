<?php
namespace SPHERE\Application\Api\Document\Custom\Zwickau\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;

/**
 * Class SchoolContract
 *
 * @package SPHERE\Application\Api\Document\Custom\Zwickau\Repository
 */
class SchoolContract extends AbstractDocument
{

    const TEXT_SIZE = '12pt';

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
        $this->FieldValue['PersonId'] = $PersonId = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        $this->FieldValue['Gender'] = false;
        if ($PersonId) {
            if (($tblPerson = Person::useService()->getPersonById($PersonId))) {
                //get Gender
                if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                    if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                        if (($tblCommonGender = $tblCommonBirthDates->getTblCommonGender())) {
                            $this->FieldValue['Gender'] = $tblCommonGender->getName();
                        }
                    }
                }
            }
        }


        // student
        $this->FieldValue['FirstLastName'] = (isset($DataPost['FirstLastName']) && $DataPost['FirstLastName'] != '' ? $DataPost['FirstLastName'] : '&nbsp;');
        $this->FieldValue['Denomination'] = (isset($DataPost['Denomination']) && $DataPost['Denomination'] != '' ? $DataPost['Denomination'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['AddressDistrict'] = (isset($DataPost['AddressDistrict']) && $DataPost['AddressDistrict'] != '' ? $DataPost['AddressDistrict'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressPLZ'] = (isset($DataPost['AddressPLZ']) && $DataPost['AddressPLZ'] != '' ? $DataPost['AddressPLZ'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['Birthplace'] = (isset($DataPost['Birthplace']) && $DataPost['Birthplace'] != '' ? $DataPost['Birthplace'] : '&nbsp;');

        // division prepare
        $this->FieldValue['ReservationDivision'] = (isset($DataPost['ReservationDivision']) && $DataPost['ReservationDivision'] != '' ? $DataPost['ReservationDivision'] : '&nbsp;');
        $this->FieldValue['ReservationDate'] = (isset($DataPost['ReservationDate']) && $DataPost['ReservationDate'] != '' ? $DataPost['ReservationDate'] : '&nbsp;');

        // custody
        $this->FieldValue['SalutationCustody1'] = (isset($DataPost['SalutationCustody1']) && $DataPost['SalutationCustody1'] != '' ? $DataPost['SalutationCustody1'] : '&nbsp;');
        $this->FieldValue['FirstLastNameCustody1'] = (isset($DataPost['FirstLastNameCustody1']) && $DataPost['FirstLastNameCustody1'] != '' ? $DataPost['FirstLastNameCustody1'] : '&nbsp;');
        $this->FieldValue['SalutationCustody2'] = (isset($DataPost['SalutationCustody2']) && $DataPost['SalutationCustody2'] != '' ? $DataPost['SalutationCustody2'] : '&nbsp;');
        $this->FieldValue['FirstLastNameCustody2'] = (isset($DataPost['FirstLastNameCustody2']) && $DataPost['FirstLastNameCustody2'] != '' ? $DataPost['FirstLastNameCustody2'] : '&nbsp;');

        // common

        $this->FieldValue['ProspectCall'] = 'den Schüler/die Schülerin';
        $this->FieldValue['ProspectCallShort'] = 'er/sie';

        $this->FieldValue['Male'] = 'false';
        $this->FieldValue['Female'] = 'false';
        if (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == 'Männlich') {
            $this->FieldValue['Male'] = 'true';
            $this->FieldValue['ProspectCall'] = 'den Schüler';
            $this->FieldValue['ProspectCallShort'] = 'er';
        } elseif (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == 'Weiblich') {
            $this->FieldValue['Female'] = 'true';
            $this->FieldValue['ProspectCall'] = 'die Schülerin';
            $this->FieldValue['ProspectCallShort'] = 'sie';
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

        return 'Schulvertrag';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
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
                                    ->styleHeight('75px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulvertrag')
                                    ->styleTextSize('15pt')
                                    ->styleTextBold()
                                    ->styleTextUnderline()
                                    ->stylePaddingBottom('34px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('zwischen')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('34px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Christen machen Schule Zwickau GmbH')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->styleTextBold()
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('als Träger der Evangelischen Schule "Stephan Roth" Zwickau')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('18px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('- vertreten durch den Geschäftsführer -')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('53px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('und')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('20px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('
                                            {% if '.$this->FieldValue['Female'].' == "true" %}
                                                der Schülerin
                                            {% else %}
                                                {% if '.$this->FieldValue['Male'].' == "true" %}
                                                    dem Schüler
                                                {% else %}
                                                    dem Schüler/der Schülerin
                                                {% endif %}
                                            {% endif %}
                                            ')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '40%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['FirstLastName'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '60%')
                                    )
                                    ->stylePaddingBottom('30px')
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('geboren am')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '16%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['Birthday'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '32%')
                                        ->addElementColumn((new Element())
                                            ->setContent('in')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '4%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['Birthplace'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '48%')
                                    )
                                    ->stylePaddingBottom('30px')
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('Konfession:')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '16%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['Denomination'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '84%')
                                    )
                                    ->stylePaddingBottom('15px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('- vertreten durch die Eltern/Personensorgeberechtigten -')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('30px')
                                    ->styleAlignCenter()
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['SalutationCustody1'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '16%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['FirstLastNameCustody1'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '59%')
                                        ->addElementColumn((new Element())
                                            ->setContent(($this->FieldValue['FirstLastNameCustody2'] != '&nbsp;' ? 'und' : ''))
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '25%')
                                    )
                                    ->stylePaddingBottom('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['SalutationCustody2'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '16%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['FirstLastNameCustody2'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '84%')
                                    )
                                    ->stylePaddingBottom('30px')
                                )
                            )
                            ->addSection((new Section())
                                ->addSliceColumn((new Slice())
                                    ->addSection((new Section())
                                        ->addElementColumn((new Element())
                                            ->setContent('wohnhaft in')
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '16%')
                                        ->addElementColumn((new Element())
                                            ->setContent($this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'].' '.$this->FieldValue['AddressStreet'])
                                            ->styleTextSize(self::TEXT_SIZE)
                                            , '84%')
                                    )
                                    ->stylePaddingBottom('30px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('wird folgender Schulvertrag rechtsverbindlich geschlossen:')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('15px')
                                    , '15%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('§ 1 Aufnahme')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->styleAlignCenter()
                                    ->styleTextUnderline()
                                    ->stylePaddingBottom('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Der Schulträger nimmt '.$this->FieldValue['ProspectCall'].' mit Wirkung 
                                    vom '.$this->FieldValue['ReservationDate'].' in die '.$this->FieldValue['ReservationDivision'].'.
                                    Klasse der Evangelischen Schule "Stephan Roth" Zwickau auf, sofern '.$this->FieldValue['ProspectCallShort'].'
                                    die von der Schulaufsicht als notwendig erklärten Voraussetzungen für die 
                                    Einschulung und die sonstigen Voraussetzungen nach diesem Vertrag erfüllt.')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('15px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('§ 2 Zielsetzung der Schule')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->styleAlignCenter()
                                    ->styleTextUnderline()
                                    ->stylePaddingBottom('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Die Erziehungs- und Unterrichtsarbeit an der Evangelischen Schule ist von den
                                    Bekenntnissen der Evangelischen Kirche her bestimmt. Eltern, Schüler/Schülerinnen 
                                    und Lehrer/Lehrerinnen verstehen sich als Schulgemeinde. Dies wird sichtbar in 
                                    Schulgottesdiensten, Andachten, diakonischen Aufgaben und anderen
                                    Einrichtungen, die den besonderen Charakter der Schule prägen. Bei allen Fragen
                                    der Schulordnung geht die Evangelische Schule "Stephan Roth" Zwickau')
                                    ->styleTextSize(self::TEXT_SIZE)
                                    ->stylePaddingBottom('10px')
                                )
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('Schulvertrag &nbsp;- Seite 1')
                                    ->styleTextSize('9pt')
                                    ->styleAlignCenter()
                                )
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