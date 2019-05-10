<?php
/**
 * Import Fakturierung. Reihenfolge der Felder aus der xlsx-Datei *.xlsx
 * wird Dynamisch ausgelesen (Erfolgt in Control)
 */

namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Text\Repository\Warning;

/**
 * Class ImportGateway
 * @package SPHERE\Application\Billing\Inventory\Import
 */
class ImportGateway extends AbstractConverter
{

    private $ResultList = array();
    private $ImportList = array();

    /**
     * LectureshipGateway constructor.
     *
     * @param string        $File SpUnterricht.csv
     * @param ImportControl $Control
     */
    public function __construct($File, ImportControl $Control)
    {
        $this->loadFile($File);

        $ColumnList = $Control->getScanResult();

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer($ColumnList['Zählung'], 'Row'));
        $this->setPointer(new FieldPointer($ColumnList['Verursacher Vorname'], 'FirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Verursacher Nachname'], 'LastName'));
        $this->setPointer(new FieldPointer($ColumnList['Geburtstag'], 'Birthday'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Geburtstag'], 'Birthday', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Beitrag'], 'Value'));
        $this->setPointer(new FieldPointer($ColumnList['Preis-Variante'], 'PriceVariant'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsart'], 'Item'));
        $this->setPointer(new FieldPointer($ColumnList['Mandatsreferenznummer'], 'Reference'));
        $this->setPointer(new FieldPointer($ColumnList['Mandatsreferenznummer Gültig ab'], 'ReferenceDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Mandatsreferenznummer Gültig ab'], 'ReferenceDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Datum beitragspflichtig von'], 'PaymentFromDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Datum beitragspflichtig von'], 'PaymentFromDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Datum beitragspflichtig bis'], 'PaymentTillDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Datum beitragspflichtig bis'], 'PaymentTillDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Zahler Vorname'], 'DebtorFirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Zahler Nachname'], 'DebtorLastName'));
        $this->setPointer(new FieldPointer($ColumnList['Debitorennummer'], 'DebtorNumber'));
        $this->setPointer(new FieldPointer($ColumnList['IBAN'], 'IBAN'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['IBAN'], 'IBAN', array($this, 'sanitizeIBAN')));
        $this->setPointer(new FieldPointer($ColumnList['BIC'], 'BIC'));
        $this->setPointer(new FieldPointer($ColumnList['Bank'], 'Bank'));
        // Beispiel funktionalität (mach noch was mit dem ausgelesenem Wert:)
        $this->setPointer(new FieldPointer($ColumnList['IBAN'], 'IBANControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['IBAN'], 'IBANControl', array($this, 'sanitizeIBANFrontend')));

        $this->scanFile(1);
    }

    /**
     * @return array
     */
    public function getResultList()
    {
        return $this->ResultList;
    }

    /**
     * @return array
     */
    public function getImportList()
    {
        return $this->ImportList;
    }

    /**
     * @param array $Row
     *
     * @return void
     */
    public function runConvert($Row)
    {
        $Result = array();
        foreach ($Row as $Part) {
            $Result = array_merge($Result, $Part);
        }

        $tblPerson = $this->getPerson($Result['FirstName'], $Result['LastName'], $Result['Birthday']);
        $tblPersonDebtor = $this->getPerson($Result['DebtorFirstName'], $Result['DebtorLastName'], '', $tblPerson);

        $ImportRow = array(
            'Row'                    => $Result['Row'],
            'FirstName'              => $Result['FirstName'],
            'LastName'               => $Result['LastName'],
            'serviceTblPerson'       => $tblPerson,
            'Birthday'               => $Result['Birthday'],
            'Value'                  => $Result['Value'],
            'PriceVariant'           => $Result['PriceVariant'],
            'Item'                   => $Result['Item'],
            'Reference'              => $Result['Reference'],
            'ReferenceDate'          => $Result['ReferenceDate'],
            'PaymentFromDate'        => $Result['PaymentFromDate'],
            'PaymentTillDate'        => $Result['PaymentTillDate'],
            'DebtorFirstName'        => $Result['DebtorFirstName'],
            'DebtorLastName'         => $Result['DebtorLastName'],
            'serviceTblPersonDebtor' => $tblPersonDebtor,
            'DebtorNumber'           => $Result['DebtorNumber'],
            'IBAN'                   => $Result['IBAN'],
            'IBANControl'            => $Result['IBANControl'],
            'BIC'                    => $Result['BIC'],
            'Bank'                   => $Result['Bank'],
        );

        $Result['PersonFrontend'] = ($tblPerson
            ? '<div class="alert alert-success" style="margin: 0; padding: 2px;">Person&nbsp;OK</div>'
            : '<div class="alert alert-danger" style="margin: 0; padding: 2px;">Person&nbsp;Error</div>');
        $Result['DebtorFrontend'] = ($tblPersonDebtor
            ? '<div class="alert alert-success" style="margin: 0; padding: 2px;">Person&nbsp;OK</div>'
            : '<div class="alert alert-danger" style="margin: 0; padding: 2px;">Person&nbsp;Error</div>');

        $this->ImportList[] = $ImportRow;
        $this->ResultList[] = $Result;
    }

    /**
     * @param string         $FirstName
     * @param string         $LastName
     * @param string         $Birthday
     * @param TblPerson|null $tblPersonStudent
     *
     * @return false|TblPerson
     */
    private function getPerson($FirstName, $LastName, $Birthday = '', TblPerson $tblPersonStudent = null)
    {

        $tblPersonList = array();
        if ($Birthday){
            $tblPersonList = Person::useService()->getPersonAllByNameAndBirthday($FirstName, $LastName, $Birthday);
        }
        if (empty($tblPersonList)){
            $tblPersonList = Person::useService()->getPersonAllByName($FirstName, $LastName);
        }
        /** @var TblPerson|false $tblPerson */
        $tblPerson = false;
        if (count($tblPersonList) == 1){
            $tblPerson = current($tblPersonList);
        } else {
            //ToDO zu viele Personen die zugeordnet sein können (bei Schülern)


            // Wenn ein Schüler vorhanden ist, kann geprüft werden ob die gefundene Person über Personenverknüpfungen miteinander verbunden ist
            // wird nur geprüft, wenn keine eindeutigen treffer vorliegen
            if($tblPersonStudent){
                foreach($tblPersonList as $tblPersonDebtor){
                    if(Relationship::useService()->getRelationshipToPersonByPersonFromAndPersonTo($tblPersonDebtor, $tblPersonStudent)){
                        $tblPerson = $tblPersonDebtor;
                        continue;
                    }
                }
            }
        }
        return $tblPerson;
    }

    /**
     * @param $Value
     *
     * @return string
     */
    protected function sanitizeIBAN($Value)
    {

        return strtoupper(str_replace(' ', '', $Value));
    }

    /**
     * @param $Value
     *
     * @return bool|false|string
     */
    protected function sanitizeDate($Value)
    {

        if(($Date = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($Value)))){
            return $Date;
        }
        return $Value;
    }

    /**
     * @param $Value
     *
     * @return Warning|string
     */
    protected function sanitizeIBANFrontend($Value)
    {
        $iban = strtoupper(str_replace(' ', '', $Value));
        // Todo Wie gehen wir mit fehlerwerten um?
        if (preg_match('!(DE)([0-9]){20}!is', $iban, $Match)){
            return $Match[0];
        }
        return new Warning($iban);
    }
}