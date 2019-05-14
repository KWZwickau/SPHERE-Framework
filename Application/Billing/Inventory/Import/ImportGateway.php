<?php
/**
 * Import Fakturierung. Reihenfolge der Felder aus der xlsx-Datei *.xlsx
 * wird Dynamisch ausgelesen (Erfolgt in Control)
 */

namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

/**
 * Class ImportGateway
 * @package SPHERE\Application\Billing\Inventory\Import
 */
class ImportGateway extends AbstractConverter
{

    private $ResultList = array();
    private $ImportList = array();
    private $ErrorCount = 0;

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
     * @return int
     */
    public function getErrorCount()
    {

        return $this->ErrorCount;
    }

    private function addErrorCount()
    {

        $this->ErrorCount++;
    }

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
        $this->setPointer(new FieldPointer($ColumnList['Verursacher Geburtstag'], 'Birthday'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Verursacher Geburtstag'], 'Birthday', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Individueller Preis'], 'Value'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Individueller Preis'], 'Value', array($this, 'sanitizePriceString')));
        $this->setPointer(new FieldPointer($ColumnList['Individueller Preis'], 'ValueControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Individueller Preis'], 'ValueControl', array($this, 'sanitizePriceStringFrontend')));
        $this->setPointer(new FieldPointer($ColumnList['Preis-Variante'], 'PriceVariant'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsart'], 'Item'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsart'], 'ItemControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Beitragsart'], 'ItemControl', array($this, 'sanitizeItem')));
        $this->setPointer(new FieldPointer($ColumnList['Mandatsreferenznummer'], 'Reference'));
        $this->setPointer(new FieldPointer($ColumnList['Mandatsref Beschreibung'], 'ReferenceDescription'));
        $this->setPointer(new FieldPointer($ColumnList['Mandatsreferenznummer gültig ab'], 'ReferenceDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Mandatsreferenznummer gültig ab'], 'ReferenceDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Datum beitragspflichtig von'], 'PaymentFromDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Datum beitragspflichtig von'], 'PaymentFromDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Datum beitragspflichtig bis'], 'PaymentTillDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Datum beitragspflichtig bis'], 'PaymentTillDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Zahler Vorname'], 'DebtorFirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Zahler Nachname'], 'DebtorLastName'));
        $this->setPointer(new FieldPointer($ColumnList['Debitorennummer'], 'DebtorNumber'));
        $this->setPointer(new FieldPointer($ColumnList['IBAN'], 'IBAN'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['IBAN'], 'IBAN', array($this, 'sanitizeTrimSpace')));
        $this->setPointer(new FieldPointer($ColumnList['BIC'], 'BIC'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['BIC'], 'BIC', array($this, 'sanitizeTrimSpace')));
        $this->setPointer(new FieldPointer($ColumnList['Bank Name'], 'Bank'));
        // Beispiel funktionalität (mach noch was mit dem ausgelesenem Wert:)
        $this->setPointer(new FieldPointer($ColumnList['IBAN'], 'IBANControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['IBAN'], 'IBANControl', array($this, 'sanitizeIBANFrontend')));
        $this->setPointer(new FieldPointer($ColumnList['BIC'], 'BICControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['BIC'], 'BICControl', array($this, 'sanitizeBICFrontend')));

        $this->scanFile(3);
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
        if(!$tblPerson){
            $tblPerson = null;
        }
        $tblPersonDebtor = $this->getPerson($Result['DebtorFirstName'], $Result['DebtorLastName'], '', $tblPerson);
        if(!$tblPersonDebtor){
            $tblPersonDebtor = null;
        }

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
            'ReferenceDescription'   => $Result['ReferenceDescription'],
            'ReferenceDate'          => $Result['ReferenceDate'],
            'PaymentFromDate'        => $Result['PaymentFromDate'],
            'PaymentTillDate'        => $Result['PaymentTillDate'],
            'DebtorFirstName'        => $Result['DebtorFirstName'],
            'DebtorLastName'         => $Result['DebtorLastName'],
            'serviceTblPersonDebtor' => $tblPersonDebtor,
            'DebtorNumber'           => $Result['DebtorNumber'],
            'IBAN'                   => $Result['IBAN'],
            'BIC'                    => $Result['BIC'],
            'Bank'                   => $Result['Bank'],
        );

        $Birthday = '';
        if($Result['Birthday']){
            $Birthday = new Muted(new Small(' ('.$Result['Birthday'].')'));
        }

        if($tblPerson){
            $PersonMessage = new Success($Result['FirstName'].'&nbsp;'.$Result['LastName'].$Birthday
                , null, false, 2, 0);
        } else {
            $this->addErrorCount();
            $PersonMessage = new ToolTip(new Danger($Result['FirstName'].'&nbsp;'.$Result['LastName'].$Birthday
                , null, false, 2, 0), 'Person nicht oder nicht eindeutig vorhanden');
        }
        $Result['PersonFrontend'] = $PersonMessage;
        if($tblPersonDebtor){
            $DebtorMessage = new Success($Result['DebtorFirstName'].'&nbsp;'.$Result['DebtorLastName']
                , null, false, 2, 0);
        } else {
            $DebtorMessage = new ToolTip(new Danger($Result['DebtorFirstName'].'&nbsp;'.$Result['DebtorLastName']
                , null, false, 2, 0), 'Person nicht oder nicht eindeutig vorhanden');
        }
        $Result['DebtorFrontend'] = $DebtorMessage;

        $IsValueNeed = true;
        $Result['ItemVariantFrontend'] = new ToolTip(new Warning($Result['PriceVariant'].'&nbsp;', null, false, 2, 0)
            , 'Preis-Variante nicht gefunden Betrag wird als Individueller Betrag angelegt!');
        if(($tblItem = Item::useService()->getItemByName($Result['Item']))){
            if(Item::useService()->getItemVariantByItemAndName($tblItem, $Result['PriceVariant'])){
                $Result['ItemVariantFrontend'] = new Success($Result['PriceVariant'], null, false, 2, 0);
                $IsValueNeed = false;
            }
        }
        $ValueFrontend = new Success($Result['ValueControl'].'&nbsp;', null, false, 2, 0);
        if($IsValueNeed){
            if(!$Result['ValueControl']){
                $ValueFrontend = new ToolTip(new Danger('&nbsp;', null, false, 2, 0), 'Der Betrag ist eine Pflichtangabe, da die Preisvariante nicht getroffen wird!');
                $this->addErrorCount();
            }
        }
        $Result['ValueFrontend'] = $ValueFrontend;

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
        if ($tblPersonList && count($tblPersonList) == 1){
            $tblPerson = current($tblPersonList);
        } elseif($tblPersonList) {
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
    protected function sanitizeTrimSpace($Value)
    {

        return strtoupper(str_replace(' ', '', $Value));
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
            // IBAN mit leerzeichen anzeigen
            $ibanDisplay = $Match[0];
            $countLetter = strlen($ibanDisplay);
            $IBANParts = array();
            for($i = 0; $i < $countLetter; $i += 4) {
                $IBANParts[] = substr($ibanDisplay, $i, 4);
            }
            $ibanDisplay = implode('&nbsp;', $IBANParts);
            return new Success($ibanDisplay, null, false, 2, 0);
        }

        // Fehlerzählung
        $this->addErrorCount();
        // IBAN mit leerzeichen anzeigen
        $countLetter = strlen($iban);
        if($countLetter >= 4){
            $IBANParts = array();
            for($i = 0; $i < $countLetter; $i += 4) {
                $IBANParts[] = substr($iban, $i, 4);
            }
            $iban = implode('&nbsp;', $IBANParts);
        }
        if($iban === ''){
            $iban = '&nbsp;';
        }
        return new ToolTip(new Danger($iban, null, false, 2, 0), 'Format oder Zeichenanzahl stimmen nicht');
    }

    /**
     * @param $Value
     *
     * @return Warning|string
     */
    protected function sanitizeBICFrontend($Value)
    {
        $bic = strtoupper(str_replace(' ', '', $Value));
        if (strlen($bic) == 11){
            // BIC mit leerzeichen anzeigen
            $bicPart[] = substr($bic, 0, 4);
            $bicPart[] = substr($bic, 4, 2);
            $bicPart[] = substr($bic, 6, 2);
            $bicPart[] = substr($bic, 8, 3);
            $bic = implode('&nbsp;', $bicPart);
            return new Success($bic, null, false, 2, 0);
        }

        // Fehlerzählung
        $this->addErrorCount();

        if($bic === ''){
            $bic = '&nbsp;';
        }
        // BIC Warnung anzeigen
        return new ToolTip(new Danger($bic, null, false, 2, 0), 'Anzahl der Zeichen stimmen nicht');
    }

    /**
     * @param $Value
     *
     * @return bool|false|string
     */
    protected function sanitizeDate($Value)
    {
        if($Value){
            if(($Date = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($Value)))){
                return $Date;
            }
        }
        return $Value;
    }

    protected function sanitizeItem($Value)
    {

        $tblItem = Item::useService()->getItemByName($Value);
        if($tblItem){
            $Message = new Success($Value, null, false, 2, 0);
        } else {
            $this->addErrorCount();
            $Message = new ToolTip(new Danger($Value, null, false, 2, 0), 'Beitragsart wurde nicht gefunden!');
        }

        return $Message;
    }

    protected function sanitizePriceString($Value)
    {

        if((int)$Value){
            $Price = number_format($Value, 2, '.', '');
            return $Price;
        }
        return $Value;
    }

    protected function sanitizePriceStringFrontend($Value)
    {

        if((int)$Value){
            $Price = number_format($Value, 2, ',', '.');
            return $Price.'&nbsp;€';
        }
        return $Value;
    }
}