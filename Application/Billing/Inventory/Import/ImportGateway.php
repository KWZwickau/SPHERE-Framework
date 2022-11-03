<?php
/**
 * Import Fakturierung. Reihenfolge der Felder aus der xlsx-Datei *.xlsx
 * wird Dynamisch ausgelesen (Erfolgt in Control)
 */

namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Transfer\Gateway\Converter\AbstractConverter;
use SPHERE\Application\Transfer\Gateway\Converter\FieldPointer;
use SPHERE\Application\Transfer\Gateway\Converter\FieldSanitizer;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
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
    private $IsError = false;
    private $IsIgnore = false;
    private $ItemName = '';
    private $DebtorNumberArray = array();

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
        $this->IsError = true;
    }

    private function addIgnore()
    {

        $this->IsIgnore = true;
    }

    /**
     * LectureshipGateway constructor.
     *
     * @param string        $File SpUnterricht.csv
     * @param ImportControl $Control
     * @param string        $Item
     */
    public function __construct($File, ImportControl $Control, $Item = '')
    {
        $this->loadFile($File);
        $this->ItemName = $Item;

        $ColumnList = $Control->getScanResult();

        $this->addSanitizer(array($this, 'sanitizeFullTrim'));

        $this->setPointer(new FieldPointer($ColumnList['Zählung'], 'Number'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsverursacher Vorname'], 'FirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsverursacher Nachname'], 'LastName'));
        if(isset($ColumnList['Beitragsverursacher Geburtstag'])){
            $this->setPointer(new FieldPointer($ColumnList['Beitragsverursacher Geburtstag'], 'Birthday'));
            $this->setSanitizer(new FieldSanitizer($ColumnList['Beitragsverursacher Geburtstag'], 'Birthday', array($this, 'sanitizeDate')));
        }
        $this->setPointer(new FieldPointer($ColumnList['Individueller Preis'], 'Value'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Individueller Preis'], 'Value', array($this, 'sanitizePriceString')));
        $this->setPointer(new FieldPointer($ColumnList['Individueller Preis'], 'ValueControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Individueller Preis'], 'ValueControl', array($this, 'sanitizePriceStringFrontend')));
        $this->setPointer(new FieldPointer($ColumnList['Preis-Variante'], 'PriceVariant'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsart'], 'Item'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragsart'], 'ItemControl'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Beitragsart'], 'ItemControl', array($this, 'sanitizeItem')));
        $this->setPointer(new FieldPointer($ColumnList['Mandatsreferenznummer'], 'Reference'));
        if(isset($ColumnList['Mandatsref Beschreibung'])){
            $this->setPointer(new FieldPointer($ColumnList['Mandatsref Beschreibung'], 'ReferenceDescription'));
        }
        $this->setPointer(new FieldPointer($ColumnList['Mandatsreferenznummer gültig ab'], 'ReferenceDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Mandatsreferenznummer gültig ab'], 'ReferenceDate', array($this, 'sanitizeDate')));
        $this->setPointer(new FieldPointer($ColumnList['Datum beitragspflichtig von'], 'PaymentFromDate'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['Datum beitragspflichtig von'], 'PaymentFromDate', array($this, 'sanitizeDate')));
        if(isset($ColumnList['Datum beitragspflichtig bis'])){
            $this->setPointer(new FieldPointer($ColumnList['Datum beitragspflichtig bis'], 'PaymentTillDate'));
            $this->setSanitizer(new FieldSanitizer($ColumnList['Datum beitragspflichtig bis'], 'PaymentTillDate', array($this, 'sanitizeDate')));
        }
        $this->setPointer(new FieldPointer($ColumnList['Beitragszahler Vorname'], 'DebtorFirstName'));
        $this->setPointer(new FieldPointer($ColumnList['Beitragszahler Nachname'], 'DebtorLastName'));
        $this->setPointer(new FieldPointer($ColumnList['Kontoinhaber'], 'Owner'));
        if(isset($ColumnList['Debitorennummer'])){
            $this->setPointer(new FieldPointer($ColumnList['Debitorennummer'], 'DebtorNumber'));
            $this->setPointer(new FieldPointer($ColumnList['Debitorennummer'], 'DebtorNumberControl'));
//            $this->setSanitizer(new FieldSanitizer($ColumnList['Debitorennummer'], 'DebtorNumberControl', array($this, 'sanitizeDebtorNumber')));
        }
        $this->setPointer(new FieldPointer($ColumnList['IBAN'], 'IBAN'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['IBAN'], 'IBAN', array($this, 'sanitizeTrimSpace')));
        $this->setPointer(new FieldPointer($ColumnList['BIC'], 'BIC'));
        $this->setSanitizer(new FieldSanitizer($ColumnList['BIC'], 'BIC', array($this, 'sanitizeTrimSpace')));
        if(isset($ColumnList['Bank Name'])){
            $this->setPointer(new FieldPointer($ColumnList['Bank Name'], 'Bank'));
        }
        if(isset($ColumnList['Zahlung Jährlich'])){
            $this->setPointer(new FieldPointer($ColumnList['Zahlung Jährlich'], 'IsYear'));
        }
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


        // Default Error Value
        $this->IsError = false;
        $Result['IsError'] = '';

        // Fehende "nicht pflicht Felder" leer hinterlegen
        if(!isset($Result['Birthday'])){
            $Result['Birthday'] = '';
        }
        if(!isset($Result['ReferenceDescription'])){
            $Result['ReferenceDescription'] = '';
        }
        if(!isset($Result['PaymentTillDate'])){
            $Result['PaymentTillDate'] = '';
        }
        if(!isset($Result['DebtorNumber'])){
            $Result['DebtorNumber'] = '';
        }
        if(!isset($Result['Bank'])){
            $Result['Bank'] = '';
        }

        $tblPerson = $this->getPerson($Result['FirstName'], $Result['LastName'], $Result['Birthday']);
        if(!$tblPerson){
            $tblPerson = null;
        }
        $tblPersonDebtor = $this->getPerson($Result['DebtorFirstName'], $Result['DebtorLastName'], '', $tblPerson);
        if(!$tblPersonDebtor){
            $tblPersonDebtor = null;
        }
        if($tblPersonDebtor && null === $tblPerson){
            $tblPerson = $this->getPerson($Result['FirstName'], $Result['LastName'], $Result['Birthday'], $tblPersonDebtor);
            if(!$tblPerson){
                $tblPerson = null;
            }
        }

        $ImportRow = array(
            'Number'                 => $Result['Number'],
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
            'Owner'                  => $Result['Owner'],
            'DebtorNumber'           => $Result['DebtorNumber'],
            'IBAN'                   => $Result['IBAN'],
            'BIC'                    => $Result['BIC'],
            'Bank'                   => $Result['Bank'],
            'IsYear'                 => $Result['IsYear'],
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
            $this->addErrorCount();
            $DebtorMessage = new ToolTip(new Danger($Result['DebtorFirstName'].'&nbsp;'.$Result['DebtorLastName']
                , null, false, 2, 0), 'Person nicht oder nicht eindeutig vorhanden');
        }
        $Result['DebtorFrontend'] = $DebtorMessage;

        $DebtorNumber = $Result['DebtorNumber'];
        $isDebtorForItem = true;
        if($Result['Item'] != $this->ItemName){
            // Debitoren, welche nicht verwendet werden können für die Validierung ignoriert werden
        $isDebtorForItem = false;
        }
        if($isDebtorForItem && ($Setting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_DEBTOR_NUMBER_COUNT))){
            if(strlen($DebtorNumber) > $Setting->getValue()){
                $this->addErrorCount();
                $Result['DebtorNumberControl'] = new ToolTip(new Danger($DebtorNumber, null, false, 2, 0), 'Debitorennummer ist zu lang (max '.$Setting->getValue().')');
            } elseif($DebtorNumber && strlen($DebtorNumber) < $Setting->getValue()) {
                $Result['DebtorNumberControl'] = new ToolTip(new Warning($DebtorNumber, null, false, 2, 0), 'Debitorennummer ist zu kurz ('.$Setting->getValue().') dies stellt aber kein Problem dar');
            } elseif(strlen($DebtorNumber) == $Setting->getValue()){
                $Result['DebtorNumberControl'] = new Success($DebtorNumber, null, false, 2, 0);
            }
        }

        // Add Check on existing
        $IsSwitchedDebtorNumber = false;
        if($isDebtorForItem && $tblPersonDebtor && ($DebtorNumberCheck = Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor))){
            if(($DebtorNumberCheckString = current($DebtorNumberCheck)->getDebtorNumber()) != $DebtorNumber){
                $IsSwitchedDebtorNumber = true;
                $this->addErrorCount();
                $Result['DebtorNumberControl'] = new ToolTip(new Danger($DebtorNumber, null, false, 2, 0),
                    'Vorhandene Debitorennummer weicht ab (' . $DebtorNumberCheckString . ')');
            }
        }
        // Add Check on reusing other Debtornumbers
        if($isDebtorForItem && !$IsSwitchedDebtorNumber && $tblPersonDebtor
            && !isset($this->DebtorNumberArray[$Result['Item'].$tblPersonDebtor->getId()])){
            $this->DebtorNumberArray[$Result['Item'].$tblPersonDebtor->getId()] = $DebtorNumber;
        } elseif($isDebtorForItem && !$IsSwitchedDebtorNumber && $tblPersonDebtor) {
            if($this->DebtorNumberArray[$Result['Item'].$tblPersonDebtor->getId()] != $DebtorNumber){
                $this->addErrorCount();
                $Result['DebtorNumberControl'] = new ToolTip(new Danger($DebtorNumber, null, false, 2, 0), 'Debtornummer unterscheidet sich im Import '
                    .$this->DebtorNumberArray[$Result['Item'].$tblPersonDebtor->getId()].' => '.$DebtorNumber);
            }
        }

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
        if($this->IsError){
            $Result['IsError'] = '<span hidden>1</span>'.new Center(new DangerText(new Disable()));
        } elseif($this->IsIgnore) {
            $Result['IsError'] = '<span hidden>0</span>'.new Center(new ToolTip(new InfoText(new Minus()), 'Zeile wird nicht importiert'));
        }

        // Import wird nur für die richtigen Beitragsarten vorgenommen
        if($Result['Item'] == $this->ItemName){
            $this->ImportList[] = $ImportRow;
        }
        $this->ResultList[] = $Result;

        $this->IsIgnore = false;


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

        $tblPerson = false;
        // bei vorhandenem Schüler wird geprüft ob Person über Personenverknüpfungen vorhanden ist
        if($tblPersonStudent){
            // Suchen einer Person, wenn gefunden dann abgleich auf Personenbeziehung
            $tblPersonGuard = Person::useService()->getPersonByName($FirstName, $LastName);
            if($tblPersonGuard && $tblPersonGuard->getId() === $tblPersonStudent->getId()){
                $tblPerson = $tblPersonGuard;
            } elseif ($tblPersonGuard){
                if(Relationship::useService()->getRelationshipToPersonByPersonFromAndPersonTo($tblPersonGuard, $tblPersonStudent)){
                    $tblPerson = $tblPersonGuard;
                    // Rückwärtssuche für den fall das Sorgeberechtigter eindeutig ist, der Schüler aber nicht
                } elseif(Relationship::useService()->getRelationshipToPersonByPersonFromAndPersonTo($tblPersonStudent, $tblPersonGuard)){
                    $tblPerson = $tblPersonGuard;
                }
            }
            if(!$tblPerson){
                // wird nur geprüft, wenn keine eindeutigen treffer vorliegen
                // Suchen aller Person, wenn gefunden dann abgleich auf Personenbeziehung
                if(($tblPersonList = Person::useService()->getPersonAllByName($FirstName, $LastName))){
                    foreach($tblPersonList as $tblPersonGuard){
                        if($tblPersonGuard && $tblPersonGuard->getId() === $tblPersonStudent->getId()){
                            $tblPerson = $tblPersonGuard;
                        } elseif (Relationship::useService()->getRelationshipToPersonByPersonFromAndPersonTo($tblPersonGuard, $tblPersonStudent)){
                            $tblPerson = $tblPersonGuard;
                            break;
                            // Rückwärtssuche für den fall das Sorgeberechtigter eindeutig ist, der Schüler aber nicht
                        } elseif(Relationship::useService()->getRelationshipToPersonByPersonFromAndPersonTo($tblPersonStudent, $tblPersonGuard)){
                            $tblPerson = $tblPersonGuard;
                            break;
                        }
                    }
                }
            }
        } else {
            // Schüler
            // oder Person ohne erkannten Schüler
            $tblPerson = Person::useService()->getPersonByName($FirstName, $LastName, $Birthday);
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
        if (preg_match('!^(DE)([0-9]){20}!is', $iban, $Match)){
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
        // Iban ohne DE am Anfang
        if(!preg_match('!^(DE)!is', $iban, $Match)){
            return new Success($iban, null, false, 2, 0);
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

        if($bic === ''){
            $bic = '&nbsp;';
        }
        if($bic === '&nbsp;'){
            // Hinweis
            return new ToolTip(new Warning($bic, null, false, 2, 0), 'Es wird empfohlen, eine BIC anzugeben');
        }
        // BIC Warnung anzeigen
        return new ToolTip(new Warning($bic, null, false, 2, 0), 'Anzahl der Zeichen stimmen nicht');
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

        if(($tblItem = Item::useService()->getItemByName($Value)) && $Value == $this->ItemName){
            $Message = new Success($Value, null, false, 2, 0);
        } else {
            $Message = new ToolTip(new Info($Value, null, false, 2, 0), 'Beitragsart wird nicht importiert');
            $this->addIgnore();
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