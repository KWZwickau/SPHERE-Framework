<?php
namespace SPHERE\Application\Api\Document\Standard\Repository;

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

        // school
        $this->FieldValue['School'] = (isset($DataPost['School']) && $DataPost['School'] != '' ? $DataPost['School'] : '&nbsp;');
        $this->FieldValue['SchoolExtended'] = (isset($DataPost['SchoolExtended']) && $DataPost['SchoolExtended'] != '' ? $DataPost['SchoolExtended'] : '&nbsp;');
        $this->FieldValue['SchoolAddressStreet'] = (isset($DataPost['SchoolAddressStreet']) && $DataPost['SchoolAddressStreet'] != '' ? $DataPost['SchoolAddressStreet'] : '&nbsp;');
        $this->FieldValue['SchoolAddressCity'] = (isset($DataPost['SchoolAddressCity']) && $DataPost['SchoolAddressCity'] != '' ? $DataPost['SchoolAddressCity'] : '&nbsp;');
        // student
        $this->FieldValue['FirstLastName'] = (isset($DataPost['FirstLastName']) && $DataPost['FirstLastName'] != '' ? $DataPost['FirstLastName'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressPLZ'] = (isset($DataPost['AddressPLZ']) && $DataPost['AddressPLZ'] != '' ? $DataPost['AddressPLZ'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['Birthplace'] = (isset($DataPost['Birthplace']) && $DataPost['Birthplace'] != '' ? $DataPost['Birthplace'] : '&nbsp;');

        $this->FieldValue['Division'] = (isset($DataPost['Division']) && $DataPost['Division'] != '' ? $DataPost['Division'] : '&nbsp;');
        $this->FieldValue['LeaveDate'] = (isset($DataPost['LeaveDate']) && $DataPost['LeaveDate'] != '' ? $DataPost['LeaveDate'] : '&nbsp;');
        // common

        $this->FieldValue['Male'] = 'false';
        $this->FieldValue['Female'] = 'false';
        if (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == 'Männlich') {
            $this->FieldValue['Male'] = 'true';
        } elseif (isset($this->FieldValue['Gender']) && $this->FieldValue['Gender'] == 'Weiblich') {
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
                                                .($this->FieldValue['SchoolAddressStreet'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressStreet'] : '')
                                                .($this->FieldValue['SchoolAddressCity'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressCity'] : '')
                                            )
                                            ->styleHeight('140px')
                                        )
                                    ), '60%'
                                )
                                ->addElementColumn($this->getPictureEnrollmentDocument()
                                    ->styleAlignRight(),'40%'
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
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                {% if '.$this->FieldValue['Female'].' == "true" %}
                                    Die Schülerin
                                {% else %}
                                    {% if '.$this->FieldValue['Male'].' == "true" %}
                                        Der Schüler
                                    {% else %}
                                        Die Schülerin/Der Schüler
                                    {% endif %}
                                {% endif %}
                            ')
                                    ->stylePaddingTop('50px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['FirstLastName'])
                                    ->stylePaddingTop('50px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('geboren am')
                                    ->stylePaddingTop('30px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Birthday'])
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('geboren in')
                                    ->stylePaddingTop('30px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Birthplace'])
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('wohnhaft')
                                    ->stylePaddingTop('30px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['AddressStreet'])
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('&nbsp;')
                                    ->stylePaddingTop('30px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'])
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('besucht zur Zeit die Klasse')
                                    ->stylePaddingTop('30px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Division'])
                                    ->stylePaddingTop('30px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent('
                                {% if '.$this->FieldValue['Female'].' == "true" %}
                                    Sie
                                {% else %}
                                    {% if '.$this->FieldValue['Male'].' == "true" %}
                                        Er
                                    {% else %}
                                        Sie/Er
                                    {% endif %}
                                {% endif %}
                                wird voraussichtlich bis zum
                            ')
                                    ->stylePaddingTop('100px')
                                    , '35%')
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['LeaveDate'])
                                    ->stylePaddingTop('100px')
                                    ->styleBorderBottom()
                                    , '65%')
                            )
                            ->addSection((new Section())
                                ->addElementColumn((new Element())
                                    ->setContent($this->FieldValue['Male'])
                                    ->setContent('
                                {% if '.$this->FieldValue['Female'].' == "true" %}
                                    Schülerin
                                {% else %}
                                    {% if '.$this->FieldValue['Male'].' == "true" %}
                                        Schüler
                                    {% else %}
                                        Schülerin/Schüler
                                    {% endif %}
                                {% endif %}
                                unserer Schule sein.
                            ')
                                    ->stylePaddingTop('30px')
                                )
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
}