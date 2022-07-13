<?php
namespace SPHERE\Application\Api\Document\Custom\Gersdorf\Repository;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;

/**
 * Class MetaDataComparison
 *
 * @package SPHERE\Application\Api\Document\Custom\Gersdorf\Repository
 */
class MetaDataComparison extends AbstractDocument
{
    /**
     * @param $personId
     */
    function __construct($Data = array())
    {

        $this->setFieldValue($Data);
    }

    /**
     * @var array
     */
    private $FieldValue = array();

    /**
     * @param $personId
     *
     * @return $this
     */
    private function setFieldValue($Data = array())
    {

        $this->FieldValue['Date'] = (new \DateTime())->format('d.m.Y');
        $this->FieldValue['Year'] = '&nbsp;';
        // Person
        $this->FieldValue['FirstName'] = $this->FieldValue['LastName'] = $this->FieldValue['Birthdate'] = '&nbsp;';
        // Address
        $this->FieldValue['Street'] = $this->FieldValue['Code'] = $this->FieldValue['City'] = $this->FieldValue['District'] = $this->FieldValue['Division'] = '&nbsp;';
        // S1
        $this->FieldValue['S1FirstName'] = $this->FieldValue['S1LastName'] = $this->FieldValue['S1Street'] = $this->FieldValue['S1Code'] =
        $this->FieldValue['S1City'] = $this->FieldValue['S1District'] = '&nbsp;';
        // S2
        $this->FieldValue['S2FirstName'] = $this->FieldValue['S2LastName'] = $this->FieldValue['S2Street'] = $this->FieldValue['S2Code'] =
        $this->FieldValue['S2City'] = $this->FieldValue['S2District'] = '&nbsp;';
        // Phone
        $this->FieldValue['PhonePrivate'] = $this->FieldValue['PhoneTwo'] = $this->FieldValue['PhoneS1'] = $this->FieldValue['PhoneS2'] =
        $this->FieldValue['Phone5'] = $this->FieldValue['Phone6'] = '&nbsp;';
        // Mail
        $this->FieldValue['Email1'] = $this->FieldValue['Email2'] = '&nbsp;';

        // getPerson
        if ($Data['Person']['Id']) {
            if (($tblPerson = Person::useService()->getPersonById($Data['Person']['Id']))) {
                $this->setPersonContent($tblPerson);
                if(($tblStudent = Student::useService()->getStudentByPerson($tblPerson))){
                    if(($tblDivision = $tblStudent->getCurrentMainDivision())){
                        $this->FieldValue['Division'] = $tblDivision->getDisplayName();
                        if(($tblYear = $tblDivision->getServiceTblYear())){
                            $this->FieldValue['Year'] = $tblYear->getYear();
                        }
                    }
                }
                if(($tblCommon = Common::useService()->getCommonByPerson($tblPerson))){
                    if(($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())){
                        $this->FieldValue['Birthdate'] = $tblCommonBirthDates->getBirthday();
                    }
                }
            }
        }
        $tblRelationshipType = Relationship::useService()->getTypeByName(TblType::IDENTIFIER_GUARDIAN);
        if(($tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson, $tblRelationshipType))){
            foreach($tblToPersonList as $tblToPerson){
                if($tblToPerson->getRanking() == 1){
                    $tblPersonS1 = $tblToPerson->getServiceTblPersonFrom();
                    $this->setPersonContent($tblPersonS1, 'S1');
                } elseif($tblToPerson->getRanking() == 2){
                    $tblPersonS2 = $tblToPerson->getServiceTblPersonFrom();
                    $this->setPersonContent($tblPersonS2, 'S2');
                }
            }
        }

        return $this;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Ranking
     *
     * @return void
     */
    private function setPersonContent(TblPerson $tblPerson, $Ranking = '')
    {

        $this->FieldValue[$Ranking.'FirstName'] = $tblPerson->getFirstSecondName();
        $this->FieldValue[$Ranking.'LastName'] = $tblPerson->getLastName();
        if(($tblAddress = Address::useService()->getAddressByPerson($tblPerson))){
            $this->FieldValue[$Ranking.'Street'] = $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
            if(($tblCity = $tblAddress->getTblCity())){
                $this->FieldValue[$Ranking.'Code'] = $tblCity->getCode();
                $this->FieldValue[$Ranking.'City'] = $tblCity->getName();
                $this->FieldValue[$Ranking.'District'] = ($tblCity->getDistrict() ?: '&nbsp;');
            }
        }
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Stammdatenabfrage';
    }

    /**
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument($pageList = array(), $Part = '0')
    {

        $TextSize = '16px';
        $SpaceLeft = '35%';
        $SpaceRight = '65%';

        return (new Frame())->addDocument((new Document())
            ->addPage((new Page())
                ->addSlice($this->setHeader($TextSize))
                ->addSlice((new Slice())
                    ->addElement((new Element())
                        ->setContent('&nbsp;')
                        ->styleTextSize('18px')
                ))
                ->addSlice($this->setStudentInfo($TextSize, $SpaceLeft, $SpaceRight))
                ->addSlice($this->setCustodyInfo('1', $TextSize, $SpaceLeft, $SpaceRight))
                ->addSlice($this->setCustodyInfo('2', $TextSize, $SpaceLeft, $SpaceRight))
                ->addSlice($this->setPhoneInfo($TextSize, $SpaceLeft, $SpaceRight))
                ->addSlice($this->setMailInfo($TextSize, $SpaceLeft, $SpaceRight))
                )
            );
    }

    /**
     * @param $TextSize
     *
     * @return Slice
     */
    private function setHeader($TextSize)
    {

        return (new Slice())
            ->addElement((new Element())
                ->setContent('Schülerdaten / Sorgeberechtigte')
                ->styleBorderAll()
                ->styleTextSize('22px')
                ->styleTextBold()
                ->styleAlignCenter()
            )
            ->addElement((new Element())
                ->setContent('Christlicher Schulverein e.V.')
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleTextSize('20px')
                ->styleAlignCenter()
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Evangelsche Oberschule Gersdorf staatlich anerkannte Ersatzschule')
                    ->styleBorderTop('2px')
                    ->styleBorderBottom('2px')
                    ->styleBorderLeft('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , '72%')
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['Year'])
                    ->styleBorderTop('2px')
                    ->styleBorderBottom('2px')
                    ->styleAlignCenter()
                    ->styleTextSize($TextSize)
                    , '14%')
                ->addElementColumn((new Element())
                    ->setContent($this->FieldValue['Date'])
                    ->styleBorderTop('2px')
                    ->styleBorderBottom('2px')
                    ->styleBorderRight('2px')
                    ->styleAlignCenter()
                    ->styleTextSize($TextSize)
                    , '14%')
            );
    }

    /**
     * @param $TextSize
     * @param $SpaceLeft
     * @param $SpaceRight
     *
     * @return Slice
     */
    private function setStudentInfo($TextSize, $SpaceLeft, $SpaceRight)
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_ Klasse')
                    ->styleBorderTop('2px')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['Division'])
                    ->styleBorderTop('2px')
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_Name')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['LastName'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_Vorname')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['FirstName'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_Geburtsdatum')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['Birthdate'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_Straße')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['Street'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_PLZ')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['Code'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_Wohnort')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['City'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Schüler_Ortsteil')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['District'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            );
    }

    /**
     * @param $Ranking
     * @param $TextSize
     * @param $SpaceLeft
     * @param $SpaceRight
     *
     * @return Slice
     */
    private function setCustodyInfo($Ranking, $TextSize, $SpaceLeft, $SpaceRight)
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sorgeberechtigter'.$Ranking.'_Name')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['S'.$Ranking.'LastName'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sorgeberechtigter'.$Ranking.'_Vorname')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['S'.$Ranking.'FirstName'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sorgeberechtigter'.$Ranking.'_Straße')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['S'.$Ranking.'Street'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sorgeberechtigter'.$Ranking.'_PLZ')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['S'.$Ranking.'Code'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sorgeberechtigter'.$Ranking.'_Wohnort')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['S'.$Ranking.'City'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Sorgeberechtigter'.$Ranking.'_Ortsteil')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' ' . $this->FieldValue['S'.$Ranking.'District'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            );
    }

    /**
     * @param $TextSize
     * @param $SpaceLeft
     * @param $SpaceRight
     *
     * @return Slice
     */
    private function setPhoneInfo($TextSize, $SpaceLeft, $SpaceRight)
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon privat')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon2')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon Vati')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon Mutti')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon 5')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Telefon 6')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            );
    }

    /**
     * @param $TextSize
     * @param $SpaceLeft
     * @param $SpaceRight
     *
     * @return Slice
     */
    private function setMailInfo($TextSize, $SpaceLeft, $SpaceRight)
    {
        return (new Slice())
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Email1')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email1'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('Email2')
                    ->styleBorderBottom()
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent(' '.$this->FieldValue['Email2'])
                    ->styleBorderBottom()
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            )
            ->addSection((new Section())
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderLeft('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceLeft)
                ->addElementColumn((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderBottom('2px')
                    ->styleBorderRight('2px')
                    ->stylePaddingLeft()
                    ->styleTextSize($TextSize)
                    , $SpaceRight)
            );
    }
}