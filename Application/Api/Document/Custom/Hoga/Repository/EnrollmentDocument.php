<?php

namespace SPHERE\Application\Api\Document\Custom\Hoga\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;

/**
 * Class EnrollmentDocument
 *
 * @package SPHERE\Application\Api\Document\Custom\Hoga\Repository
 */
class EnrollmentDocument extends AbstractDocument
{
    /**
     * @param array $Data
     */
    function __construct(array $Data)
    {
        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private array $FieldValue = array();

    /**
     * @param $DataPost
     *
     * @return \SPHERE\Application\Api\Document\Custom\Hoga\Repository\EnrollmentDocument
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
        $this->FieldValue['SchoolAddressDistrict'] = (isset($DataPost['SchoolAddressDistrict']) && $DataPost['SchoolAddressDistrict'] != '' ? $DataPost['SchoolAddressDistrict'] : '&nbsp;');
        $this->FieldValue['SchoolAddressStreet'] = (isset($DataPost['SchoolAddressStreet']) && $DataPost['SchoolAddressStreet'] != '' ? $DataPost['SchoolAddressStreet'] : '&nbsp;');
        $this->FieldValue['SchoolAddressCity'] = (isset($DataPost['SchoolAddressCity']) && $DataPost['SchoolAddressCity'] != '' ? $DataPost['SchoolAddressCity'] : '&nbsp;');

        // student
        $this->FieldValue['FirstLastName'] = (isset($DataPost['FirstLastName']) && $DataPost['FirstLastName'] != '' ? $DataPost['FirstLastName'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['AddressDistrict'] = (isset($DataPost['AddressDistrict']) && $DataPost['AddressDistrict'] != '' ? $DataPost['AddressDistrict'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressPLZ'] = (isset($DataPost['AddressPLZ']) && $DataPost['AddressPLZ'] != '' ? $DataPost['AddressPLZ'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        $this->FieldValue['Birthday'] = (isset($DataPost['Birthday']) && $DataPost['Birthday'] != '' ? $DataPost['Birthday'] : '&nbsp;');
        $this->FieldValue['Birthplace'] = (isset($DataPost['Birthplace']) && $DataPost['Birthplace'] != '' ? $DataPost['Birthplace'] : '&nbsp;');

        // set position for address
        if($this->FieldValue['AddressDistrict'] != '&nbsp;'){
            $this->FieldValue['AddressFirstLine'] = $this->FieldValue['AddressDistrict'];
            $this->FieldValue['AddressSecondLine'] = $this->FieldValue['AddressStreet'];
            $this->FieldValue['AddressThirdLine'] = $this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'];
        } else {
            $this->FieldValue['AddressFirstLine'] = $this->FieldValue['AddressStreet'];
            $this->FieldValue['AddressSecondLine'] = $this->FieldValue['AddressPLZ'].' '.$this->FieldValue['AddressCity'];
            $this->FieldValue['AddressThirdLine'] = '&nbsp;';
        }

        $this->FieldValue['Division'] = (isset($DataPost['Division']) && $DataPost['Division'] != '' ? $DataPost['Division'] : '&nbsp;');
        $this->FieldValue['DivisionId'] = (isset($DataPost['DivisionId']) && $DataPost['DivisionId'] != '' ? $DataPost['DivisionId'] : '&nbsp;');
        $this->FieldValue['LeaveDate'] = (isset($DataPost['LeaveDate']) && $DataPost['LeaveDate'] != '' ? $DataPost['LeaveDate'] : '&nbsp;');
        $this->FieldValue['ArriveDate'] = (isset($DataPost['ArriveDate']) && $DataPost['ArriveDate'] != '' ? $DataPost['ArriveDate'] : '&nbsp;');
        if (isset($DataPost['TechnicalSubjectArea'])) {
            $this->FieldValue['TechnicalSubjectArea'] = $DataPost['TechnicalSubjectArea'] != '' ? $DataPost['TechnicalSubjectArea'] : '&nbsp;';
        }
        if (isset($DataPost['EducationPay'])) {
            $this->FieldValue['EducationPay'] = $DataPost['EducationPay'] != '' ? $DataPost['EducationPay'] : '&nbsp;';
        }

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
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0')
    {
        $width = 793.5;
        $InjectStyle = 'body { margin-top: -1.2cm !important; margin-bottom: -1.2cm !important; margin-left: -1.2cm !important; margin-right: -1.2cm !important; }';

        $widthLeft = '50%';
        $widthRight = '50%';

        $borderLeft = '10%';
        $borderRight = '25%';

        // Abschluss
        $diploma = '';
        $leaveDateLabel = 'und wird voraussichtlich bis zum';

        if (isset($this->FieldValue['DivisionId'])
            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($this->FieldValue['DivisionId']))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && isset($this->FieldValue['PersonId'])
            && ($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))
            && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblType = $tblStudentEducation->getServiceTblSchoolType())
        ) {
            switch ($tblType->getShortName()) {
                case 'Gy':
                    $leaveDateLabel = 'und wird diese voraussichtlich zum';
                    $diploma = 'vorbehaltlich vorzeitiger Kündigung mit einer allgemeinen Hochschulreife (Abitur) verlassen.';
                    break;
                case 'OS':
                    $leaveDateLabel = 'und wird diese voraussichtlich zum';
                    if (($tblCourse = $tblStudentEducation->getServiceTblCourse())) {
                        $leaveDateLabel = 'und wird diese voraussichtlich zum';
                        if ($tblCourse->getName() == 'Realschule') {
                            $diploma = 'vorbehaltlich vorzeitiger Kündigung mit einem Realschulabschluss verlassen.';
                        } else {
                            $diploma = 'vorbehaltlich vorzeitiger Kündigung mit einem Hauptschulabschluss verlassen.';
                        }
                    }
                    break;
                case 'BGy':
                    $leaveDateLabel = 'und wird diese voraussichtlich zum';
                    $diploma = '- vorbehaltlich vorzeitiger Kündigung - mit einem allgemeinen Hochschulabschluss (Abitur)
                        verlassen.';
                    break;
                case 'FOS':
                    $leaveDateLabel = 'und wird diese voraussichtlich zum';
                    $diploma = '- vorbehaltlich vorzeitiger Kündigung - mit der Fachhochschulreife (Fachabitur) verlassen.';
                    break;
            }
        }

        $sectionList = array();
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleHeight('80px')
            );
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['School']
                    .($this->FieldValue['SchoolExtended'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolExtended'] : '')
                    .($this->FieldValue['SchoolAddressDistrict'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressDistrict'] : '')
                    .($this->FieldValue['SchoolAddressStreet'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressStreet'] : '')
                    .($this->FieldValue['SchoolAddressCity'] != '&nbsp;' ? '<br/>'.$this->FieldValue['SchoolAddressCity'] : '')
                )
                ->styleHeight('170px')
            );
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('Schulbescheinigung')
                ->styleTextSize('25px')
                ->styleTextBold()
                ->styleTextItalic()
            );
        $sectionList[] = (new Section())
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
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['FirstLastName'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Birthday'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren in')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Birthplace'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('wohnhaft')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressFirstLine'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressSecondLine'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressThirdLine'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('besucht zur Zeit die Klasse')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Division'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
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
                    besucht unsere Schule seit dem
                ')
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['ArriveDate'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);

        if (isset($this->FieldValue['TechnicalSubjectArea'])) {
            $sectionList[] = (new Section())
                ->addElementColumn((new Element())
                    ->setContent('in der Fachrichtung')
                    ->stylePaddingTop('30px')
                    , $widthLeft)
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['TechnicalSubjectArea'])
                    ->stylePaddingTop('30px')
                    ->styleBorderBottom()
                    , $widthRight);
        }

        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent($leaveDateLabel)
                ->stylePaddingTop('30px')
                , $widthLeft)
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['LeaveDate'])
                ->stylePaddingTop('30px')
                ->styleBorderBottom()
                , $widthRight);
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Male'])
                ->setContent(
                    $diploma == ''
                        ? '{% if '.$this->FieldValue['Female'].' == "true" %}
                                Schülerin
                            {% else %}
                                {% if '.$this->FieldValue['Male'].' == "true" %}
                                    Schüler
                                {% else %}
                                    Schülerin/Schüler
                                {% endif %}
                            {% endif %}
                            unserer Schule sein.'
                        : $diploma
                )
                ->stylePaddingTop('30px')
            );

        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent(isset($this->FieldValue['EducationPay'])
                    ? $this->FieldValue['EducationPay']
                    : '&nbsp;'
                )
                ->stylePaddingTop('30px')
            );

        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Place'].$this->FieldValue['Date'])
                ->stylePaddingTop('50px')
                ->styleBorderBottom()
                , '45%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('50px')
            );
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('Ort, Datum')
                ->styleTextSize('12px')
                , '45%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
            );
        $sectionList[] = (new Section())
            ->addElementColumn((new Element())
                ->setContent('Dieses Schreiben wurde maschinell erstellt und ist ohne Unterschrift gültig.')
                ->styleMarginTop('15px')
            );

        $slice = (new Slice())->addSectionList($sectionList);

        return (new Frame($InjectStyle))->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn(
                            (new Element\Image(
                                '/Common/Style/Resource/Document/Hoga/HOGA-Briefbogen_2019_allgemein_ohne.png',
                                $width . 'px',
                                ($width * 1.414) . 'px')
                            )
                            ->styleHeight('0px')
                        )
                    )
                )
                ->addSlice((new Slice())
                    ->addSection((new Section())
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), $borderLeft
                        )
                        ->addSliceColumn($slice)
                        ->addElementColumn((new Element())
                            ->setContent('&nbsp;'), $borderRight
                        )
                    )
                )
            )
        );
    }
}