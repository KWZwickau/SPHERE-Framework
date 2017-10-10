<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer;

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
 * Class StudentTransfer
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer
 */
class StudentTransfer extends AbstractDocument
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
    private $TextPaddingLeft = '5px';


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
        // Header
        $this->FieldValue['LeaveSchool'] = (isset($DataPost['LeaveSchool']) && $DataPost['LeaveSchool'] != '' ? $DataPost['LeaveSchool'] : '&nbsp;');
        $this->FieldValue['ContactPerson'] = (isset($DataPost['ContactPerson']) && $DataPost['ContactPerson'] != '' ? $DataPost['ContactPerson'] : '&nbsp;');
        $this->FieldValue['DocumentNumber'] = (isset($DataPost['DocumentNumber']) && $DataPost['DocumentNumber'] != '' ? $DataPost['DocumentNumber'] : '&nbsp;');
        $this->FieldValue['Phone'] = (isset($DataPost['Phone']) && $DataPost['Phone'] != '' ? $DataPost['Phone'] : '&nbsp;');
        $this->FieldValue['Fax'] = (isset($DataPost['Fax']) && $DataPost['Fax'] != '' ? $DataPost['Fax'] : '&nbsp;');
        $this->FieldValue['AddressStreet'] = (isset($DataPost['AddressStreet']) && $DataPost['AddressStreet'] != '' ? $DataPost['AddressStreet'] : '&nbsp;');
        $this->FieldValue['AddressCity'] = (isset($DataPost['AddressCity']) && $DataPost['AddressCity'] != '' ? $DataPost['AddressCity'] : '&nbsp;');
        $this->FieldValue['Date'] = (isset($DataPost['Date']) && $DataPost['Date'] != '' ? $DataPost['Date'] : '&nbsp;');
        // New School
        $this->FieldValue['NewSchool1'] = (isset($DataPost['NewSchool1']) && $DataPost['NewSchool1'] != '' ? $DataPost['NewSchool1'] : '&nbsp;');
        $this->FieldValue['NewSchool2'] = (isset($DataPost['NewSchool2']) && $DataPost['NewSchool2'] != '' ? $DataPost['NewSchool2'] : '&nbsp;');
        $this->FieldValue['NewSchool3'] = (isset($DataPost['NewSchool3']) && $DataPost['NewSchool3'] != '' ? $DataPost['NewSchool3'] : '&nbsp;');
        // Student information
        $this->FieldValue['LastFirstName'] = (isset($DataPost['LastFirstName']) && $DataPost['LastFirstName'] != '' ? $DataPost['LastFirstName'] : '&nbsp;');
        $this->FieldValue['MainAddress'] = (isset($DataPost['MainAddress']) && $DataPost['MainAddress'] != '' ? $DataPost['MainAddress'] : '&nbsp;');
        $this->FieldValue['NewAddress'] = (isset($DataPost['NewAddress']) && $DataPost['NewAddress'] != '' ? $DataPost['NewAddress'] : '&nbsp;');
        $this->FieldValue['Custody'] = (isset($DataPost['Custody']) && $DataPost['Custody'] != '' ? $DataPost['Custody'] : '&nbsp;');
        $this->FieldValue['Division'] = (isset($DataPost['Division']) && $DataPost['Division'] != '' ? $DataPost['Division'] : '___');
        $this->FieldValue['DateUntil'] = (isset($DataPost['DateUntil']) && $DataPost['DateUntil'] != '' ? $DataPost['DateUntil'] : '__________');
        $this->FieldValue['SchoolEntry'] = (isset($DataPost['SchoolEntry']) && $DataPost['SchoolEntry'] != '' ? $DataPost['SchoolEntry'] : '&nbsp;');
        $this->FieldValue['SchoolEntryDivision'] = (isset($DataPost['SchoolEntryDivision']) && $DataPost['SchoolEntryDivision'] != '' ? $DataPost['SchoolEntryDivision'] : '&nbsp;');
        $this->FieldValue['DivisionRepeat'] = (isset($DataPost['DivisionRepeat']) && $DataPost['DivisionRepeat'] != '' ? $DataPost['DivisionRepeat'] : '&nbsp;');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {

        return 'Schülerüberweisung';
    }

    /**
     * @param array $pageList
     *
     * @return Frame
     */
    public function buildDocument($pageList = array())
    {
        return (new Frame())->addDocument((new Document())
            ->addPage($this->buildPage())
        );
    }

    /**
     * @return Slice
     */
    public function getStudentTransfer()
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Test')
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getStudentTransferHead()
    {
        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Abgebende Schule')
            ->styleBorderTop()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['LeaveSchool'])
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Ansprechpartner')
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent('Aktenzeichen')
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent('Telefon')
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent('Telefax')
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '25%'
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['ContactPerson'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['DocumentNumber'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Phone'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '25%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Fax'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '25%'
            )
        );
        $Slice->addElement((new Element())
            ->setContent('Straße, Nummer')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['AddressStreet'])
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('PLZ, Ort')
                ->styleBorderLeft()
                ->styleTextSize('8pt')
                , '75%'
            )
            ->addElementColumn((new Element())
                ->setContent('Datum')
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '25%'
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['AddressCity'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderLeft()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '75%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['Date'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '25%'
            )
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getStudentTransferNewSchool()
    {
        $Slice = new Slice();
        $Slice->addSection((new Section())
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('Aufnehmende Schule')
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleTextSize('8pt')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['NewSchool1'])
                    ->stylePaddingLeft($this->TextPaddingLeft)
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleTextSize('12pt')
                )
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleTextSize('8pt')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['NewSchool2'])
                    ->stylePaddingLeft($this->TextPaddingLeft)
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleTextSize('12pt')
                )
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleTextSize('8pt')
                )
                ->addElement((new Element())
                    ->setContent($this->FieldValue['NewSchool3'])
                    ->stylePaddingLeft($this->TextPaddingLeft)
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleTextSize('12pt')
                )
                , '49%'
            )
            ->addElementColumn((new Element())
                ->setContent('&nbsp;')
                , '2%'
            )
            ->addSliceColumn((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('35px')
                    ->styleBorderTop()
                    ->styleBorderLeft()
                    ->styleBorderRight()
                )
                ->addElement((new Element())
                    ->setContent('Schülerüberweisung')
                    ->stylePaddingLeft($this->TextPaddingLeft)
                    ->styleHeight('33px')
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleTextSize('18pt')
                    ->styleTextBold()
                    ->styleAlignCenter()
                )
                ->addElement((new Element())
                    ->setContent('(Ausfertigung für die aufnehmende Schule)')
                    ->stylePaddingLeft($this->TextPaddingLeft)
                    ->styleHeight('31.8px')
                    ->styleBorderLeft()
                    ->styleBorderRight()
                    ->styleBorderBottom()
                    ->styleTextSize('11pt')
                    ->styleAlignCenter()
                )
                , '49%'
            )
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getStudentTransferStudent()
    {

        if ($this->FieldValue['Gender'] == 'Männlich') {
            $GenderString = 'des Schülers';
        } elseif ($this->FieldValue['Gender'] == 'Weiblich') {
            $GenderString = 'der Schülerin';
        } else {
            $GenderString = 'des Schülers/ der Schülerin';
        }

        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Name, Vorname '.$GenderString)
            ->styleBorderTop()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['LastFirstName'])
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addElement((new Element())
            ->setContent('bisherige Anschrift')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['MainAddress'])
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addElement((new Element())
            ->setContent('neue Anschrift')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent($this->FieldValue['NewAddress'])
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addElement((new Element())
            ->setContent('Erziehungsberechtigte')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent(nl2br($this->FieldValue['Custody']))
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->styleHeight('63px')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('12pt')
        );
        $Slice->addElement((new Element())
            ->setContent('besuchte an unserer Schule die Klasse '.$this->FieldValue['Division'].' bis zum '
                .$this->FieldValue['DateUntil'].' und wird an Ihre Schule überwiesen.')
            ->stylePaddingLeft($this->TextPaddingLeft)
            ->stylePaddingTop('3px')
            ->stylePaddingBottom('3px')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('11pt')
        );

        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getStudentTransferStudentDivision()
    {

        $Slice = new Slice();
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent('Eintritt in unsere Schule')
                ->styleBorderTop()
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '33%'
            )
            ->addElementColumn((new Element())
                ->setContent('In Klasse')
                ->styleBorderTop()
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '33%'
            )
            ->addElementColumn((new Element())
                ->setContent('wiederholte Klassen')
                ->styleBorderTop()
                ->styleBorderRight()
                ->styleTextSize('8pt')
                , '34%'
            )
        );
        $Slice->addSection((new Section())
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['SchoolEntry'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderLeft()
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '33%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['SchoolEntryDivision'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '33%'
            )
            ->addElementColumn((new Element())
                ->setContent($this->FieldValue['DivisionRepeat'])
                ->stylePaddingLeft($this->TextPaddingLeft)
                ->styleBorderRight()
                ->styleBorderBottom()
                ->styleTextSize('12pt')
                , '34%'
            )
        );
        return $Slice;
    }

    /**
     * @return Slice
     */
    private function getStudentTransferFooter()
    {

        $Slice = new Slice();
        $Slice->addElement((new Element())
            ->setContent('Mit freundlichen Grüßen')
            ->styleTextSize('16pt')
        );
        $Slice->addElement((new Element())
            ->setContent('Schulleitung der abgebenden Schule')
            ->styleBorderTop()
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleTextSize('8pt')
        );
        $Slice->addElement((new Element())
            ->setContent('(Siegel)')
            ->styleAlignRight()
            ->stylePaddingTop('5px')
            ->stylePaddingRight('35px')
            ->styleHeight('40px')
            ->styleBorderLeft()
            ->styleBorderRight()
            ->styleBorderBottom()
            ->styleTextSize('14pt')
        );
        $Slice->addElement((new Element())
            ->setContent('&nbsp;')
            ->styleHeight('30px')
        );
        $Slice->addElement((new Element())
            ->setContent('Anlagen')
            ->styleBorderAll()
            ->styleTextSize('8pt')
            ->styleHeight('40px')
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
                        , '2%'
                    )
                    ->addSliceColumn(
                        $this->getStudentTransferHead()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('35px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn(
                        $this->getStudentTransferNewSchool()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('95px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn(
                        $this->getStudentTransferStudent()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('50px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn(
                        $this->getStudentTransferStudentDivision()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                )
            )
            ->addSlice((new Slice())
                ->addElement((new Element())
                    ->setContent('&nbsp;')
                    ->styleHeight('180px')
                )
            )
            ->addSlice((new Slice())
                ->addSection((new Section())
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '2%'
                    )
                    ->addSliceColumn(
                        $this->getStudentTransferFooter()
                    )
                    ->addElementColumn((new Element())
                        ->setContent('&nbsp;')
                        , '20%'
                    )
                )
            );
    }
}