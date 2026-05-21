<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\SignInCertificate;

use SPHERE\Application\Api\Document\AbstractDocument;
use SPHERE\Application\Document\Generator\Repository\Document;
use SPHERE\Application\Document\Generator\Repository\Element;
use SPHERE\Application\Document\Generator\Repository\Frame;
use SPHERE\Application\Document\Generator\Repository\Page;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Setting\Consumer\Consumer;

class SignInCertificate extends AbstractDocument
{
    /**
     * StudentTransfer constructor.
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
     * @var string
     */
    private $TextPaddingLeft = '10px';


    /**
     * @param $DataPost
     *
     * @return $this
     */
    private function setFieldValue($DataPost)
    {
        // PersonGender
        $this->FieldValue['Gender'] = '';
        $this->FieldValue['PersonId'] = (isset($DataPost['PersonId']) && $DataPost['PersonId'] != '' ? $DataPost['PersonId'] : false);
        if ($this->FieldValue['PersonId'] && ($tblPerson = Person::useService()->getPersonById($this->FieldValue['PersonId']))) {
            if (($tblCommon = Common::useService()->getCommonByPerson($tblPerson))) {
                if (($tblCommonBirthDates = $tblCommon->getTblCommonBirthDates())) {
                    if (($tblGender = $tblCommonBirthDates->getTblCommonGender())) {
                        $this->FieldValue['Gender'] = $tblGender->getName();
                    }
                }
            }
        }

        // sign in School
        $this->FieldValue['School1'] = (isset($DataPost['School1']) && $DataPost['School1'] != '' ? $DataPost['School1'] : '&nbsp;');
        $this->FieldValue['School2'] = (isset($DataPost['School2']) && $DataPost['School2'] != '' ? $DataPost['School2'] : '&nbsp;');
        $this->FieldValue['SchoolAddressStreet'] = (isset($DataPost['SchoolAddressStreet']) && $DataPost['SchoolAddressStreet'] != '' ? $DataPost['SchoolAddressStreet'] : '&nbsp;');
        $this->FieldValue['SchoolAddressCity'] = (isset($DataPost['SchoolAddressCity']) && $DataPost['SchoolAddressCity'] != '' ? $DataPost['SchoolAddressCity'] : '&nbsp;');

        // Student information
        $this->FieldValue['FirstLastName'] = (isset($DataPost['FirstLastName']) && $DataPost['FirstLastName'] != '' ? $DataPost['FirstLastName'] : '&nbsp;');
        $this->FieldValue['BirthDate'] = (isset($DataPost['BirthDate']) && $DataPost['BirthDate'] != '' ? $DataPost['BirthDate'] : '&nbsp;');
        $this->FieldValue['BirthPlace'] = (isset($DataPost['BirthPlace']) && $DataPost['BirthPlace'] != '' ? $DataPost['BirthPlace'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        $this->FieldValue['SchoolEntry'] = (isset($DataPost['SchoolEntry']) && $DataPost['SchoolEntry'] != '' ? $DataPost['SchoolEntry'] : '&nbsp;');
        $this->FieldValue['SchoolUntil'] = (isset($DataPost['SchoolUntil']) && $DataPost['SchoolUntil'] != '' ? $DataPost['SchoolUntil'] : '&nbsp;');

        $this->FieldValue['PlaceDate'] = (isset($DataPost['PlaceDate']) && $DataPost['PlaceDate'] != '' ? $DataPost['PlaceDate'] : '&nbsp;');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Anmeldebescheinigung';
    }

    /**
     *
     * @param array $pageList
     * @param string $Part
     *
     * @return Frame
     */
    public function buildDocument(array $pageList = array(), string $Part = '0'): Frame
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Slice
     */
    private function getHeadSignOut()
    {

        $Slice = new Slice();
        $Slice->addSection((new Section())
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Aufnehmende Schule:')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['School1'])
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['School2'])
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['SchoolAddressStreet'])
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['SchoolAddressCity'])
                )
                , '60%')
            ->addSliceColumn((new Slice())
                ->addElement($this->getPictureSignOut())
                , '40%')

        );
        $Slice->addElement((new Element())
            ->setContent('Anmeldebescheinigung')
            ->styleTextSize('14pt')
            ->styleTextBold()
            ->styleAlignCenter()
        );
        return $Slice;
    }

    private function getSignIn()
    {

        $Slice = new Slice();
        $StudentCall = 'Der/Die';
        if ($this->FieldValue['Gender'] == 'Männlich') {
            $StudentCall = 'Der';
        } elseif ($this->FieldValue['Gender'] == 'Weiblich') {
            $StudentCall = 'Die';
        }
        $Slice->addElement((new Element())
            ->setContent($StudentCall . ' Schulpflichtige/Berufsschulpflichtige')
            ->stylePaddingTop('20px')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Vor- und Zuname')
                ->stylePaddingTop('20px')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['FirstLastName'])
                ->stylePaddingTop('20px')
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->stylePaddingBottom()
                ->styleBorderBottom()
                , '75%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('geboren am')
                ->stylePaddingTop('15px')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['BirthDate'])
                ->stylePaddingTop('15px')
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->stylePaddingBottom()
                ->styleBorderBottom()
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('in')
                ->styleAlignCenter()
                ->stylePaddingTop('15px')
                , '5%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['BirthPlace'])
                ->stylePaddingTop('15px')
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->stylePaddingBottom()
                ->styleBorderBottom()
                , '50%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('wohnhaft in')
                ->stylePaddingTop('15px')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressStreet'])
                ->stylePaddingTop('15px')
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->stylePaddingBottom()
                ->styleBorderBottom()
                , '75%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->stylePaddingTop('15px')
                , '25%')
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressCity'])
                ->stylePaddingTop('15px')
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->stylePaddingBottom()
                ->styleBorderBottom()
                , '75%')
        );
        $Slice->addElement((new Element())
            ->setContent('hat sich an der Schule angemeldet.')
            ->stylePaddingTop('15px')
            ->stylePaddingBottom('35px')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['PlaceDate'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->stylePaddingBottom()
                ->styleBorderBottom()
                , '40%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                ->styleBorderBottom()
                , '40%')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Ort, Datum')
                ->stylePaddingTop()
                ->styleTextSize('8pt')
                ->styleAlignCenter()
                , '40%')
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '20%')
            ->addElementColumn((new Element())
                ->setContent('Stempel und Unterschrift')
                ->stylePaddingTop()
                ->styleTextSize('8pt')
                ->styleAlignCenter()
                , '40%')
        );
        $Slice->addElement((new Element())
            ->setContent('Die Anmeldebestätigung ist umgehend an die abgebende Schule zu schicken.')
            ->stylePaddingTop('25px')
            ->styleTextBold()
        );

        return $Slice;
    }

    /**
     * @return Page
     */
    public function buildPage()
    {
        return (new Page())
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '7%'
                    )
                    ->addSliceColumn(
                        $this->getHeadSignOut()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '7%'
                    )
                    ->addSliceColumn(
                        $this->getSignIn()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
            );
    }

    /**
     * @param string $with
     *
     * @return Element|Element\Image
     */
    protected function getPictureSignOut($with = 'auto')
    {

        $picturePath = $this->getSignOutCertificateDocumentUsedPicture();
        if ($picturePath != '') {
            $height = $this->getSignOutCertificateDocumentPictureHeight();
            $column = (new Element\Image($picturePath, $with, $height))
                ->styleAlignRight();
        } else {
            $column = (new Element())
                ->setContent('&nbsp;');
        }
        return $column;
    }

    /**
     * @return string
     */
    private function getSignOutCertificateDocumentUsedPicture()
    {
        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'SignOutCertificate_PictureAddress'))
        ) {
            return (string)$tblSetting->getValue();
        }
        return '';
    }

    /**
     * @return string
     */
    private function getSignOutCertificateDocumentPictureHeight()
    {

        $value = '';

        if (($tblSetting = Consumer::useService()->getSetting(
            'Api', 'Document', 'Standard', 'SignOutCertificate_PictureHeight'))
        ) {
            $value = (string)$tblSetting->getValue();
        }

        return ($value ? $value : '80px');
    }
}