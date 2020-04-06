<?php
namespace SPHERE\Application\Transfer\Import\Standard;

use DateTime;
use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType as TblTypePhone;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblType as TblTypeRelationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Accordion;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Standard
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     * @param array|null          $Data
     *
     * @return string
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null,
        $Data = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if ($File->getError()) {
            $Form->setError('File', 'Fehler');
            $Form->appendGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('File nicht gefunden')))));
            return $Form;
        }
        if(isset($Data['Year']) && !empty($Data['Year'])){
            $YearString = substr($Data['Year'], 0, 4);
        } else {
            // fallback
            $YearString = (new DateTime())->format('Y');
        }

        /**
         * Prepare
         */
        $File = $File->move($File->getPath(),
            $File->getFilename().'.'.$File->getClientOriginalExtension());

        /**
         * Read
         */
        //$File->getMimeType()
        /** @var PhpExcel $Document */
        $Document = Document::getDocument($File->getPathname());
        if (!$Document instanceof PhpExcel) {
            $Form->setError('File', 'Fehler');
            return $Form;
        }

        $X = $Document->getSheetColumnCount();
        $Y = $Document->getSheetRowCount();

        /**
         * Header -> Location
         */
        $Location = array(
            'Nr'                  => null,
            'Bezug_Nr.'        => null,
            'Schüler_ID'          => null,
            // name
            'Geschlecht'          => null,
            'Name'                => '',
            'Vorname'             => null,
            '2ter_Vorname'          => null,
            'Rufname'             => null,
            // common
            'Geburtsdatum'        => null,
            'Geburtsort'          => null,
            'Staatsangehörigkeit' => null,
            // address
            'PLZ'                 => null,
            'Ort'                 => null,
            'Ortsteil'            => null,
            'Straße'              => null,
            'HNR'                 => null,
            // contact
            'Notfall_Festnetz'          => null,
            'Notfall_Mobil'            => null,
            'Privat_Festnetz'          => null,
            'Privat_Mobil'    => null,
            'E_Mail_Privat'    => null,

            // S1
            'S1_Anrede' => null,    // Schülerimport Spalte wird daher nicht benötigt! es sind alles "Schüler"
            'S1_Titel' => null,
            'S1_Name' => null,
            'S1_Vorname' => null,
            // adress
            'S1_PLZ' => null,
            'S1_Ort' => null,
            'S1_Ortsteil' => null,
            'S1_Straße' => null,
            'S1_HNR' => null,
            // contact
            'S1_Geschäftlich_Festnetz' => null,
            'S1_Geschäftlich_Mobil' => null,
            'S1_Notfall_Festnetz' => null,
            'S1_Notfall_Mobil' => null,
            'S1_Privat_Festnetz' => null,
            'S1_Privat_Mobil' => null,
            'S1_E_Mail_Geschäftlich' => null,
            'S1_E_Mail_Privat' => null,
            // common
            'S1_Mitarbeitbereitschaft' => null,
            'S1_Mitgliedsnummer' => null,
            // custody
            'S1_Beruf' => null,
            'S1_Arbeitsstelle' => null,
            'S1_Bemerkungen' => null,
            // account
            'S1_IBAN' => null,
            'S1_BIC' => null,
            'S1_Bankname' => null,

            // S2
            'S2_Anrede' => null,
            'S2_Titel' => null,
            'S2_Name' => null,
            'S2_Vorname' => null,
            // adress
            'S2_PLZ' => null,
            'S2_Ort' => null,
            'S2_Ortsteil' => null,
            'S2_Straße' => null,
            'S2_HNR' => null,
            // contact
            'S2_Geschäftlich_Festnetz' => null,
            'S2_Geschäftlich_Mobil' => null,
            'S2_Notfall_Festnetz' => null,
            'S2_Notfall_Mobil' => null,
            'S2_Privat_Festnetz' => null,
            'S2_Privat_Mobil' => null,
            'S2_E_Mail_Geschäftlich' => null,
            'S2_E_Mail_Privat' => null,
            // common
            'S2_Mitarbeitbereitschaft' => null,
            'S2_Mitgliedsnummer' => null,
            // custody
            'S2_Beruf' => null,
            'S2_Arbeitsstelle' => null,
            'S2_Bemerkungen' => null,
            // account
            'S2_IBAN' => null,
            'S2_BIC' => null,
            'S2_Bankname' => null,

            // maybe S3 ?

            'Klasse/Kurs' => null,
            'Schulart' => null,
            'Stammgruppe' => null,
                'Ersteinschlung_Datum' => null,
            'Allergien' => null,
            'Medikamente' => null,
            'Krankenkasse' => null,
            'Hort' => null,
            'Abholberechtigte' => null,

        );

        for ($RunX = 0; $RunX < $X; $RunX++) {
            $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
            if (array_key_exists($Value, $Location)) {
                $Location[$Value] = $RunX;
            }
        }

        /**
         * Import
         * Es müssen alle Spalten vorhanden sein
         */
        if (!in_array(null, $Location, true)) {
            $countStudent = 0;
            $countS1 = 0;
            $countS2 = 0;
            $countS1Exists = 0;
            $countS2Exists = 0;

            $error = array();
            $info = array();
            for ($RunY = 1; $RunY < $Y; $RunY++) {
                set_time_limit(300);
                // Student ---------------------------------------------------------------------------------------------
                $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                if ($firstName === '' || $lastName === '') {
                    $error[] = new DangerText('Zeile: '.($RunY)).' Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                    continue;
                }
                // person check
                $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                $tblPerson = Person::useService()->existsPerson($firstName, $lastName, $cityCode);
                if($tblPerson){
                    $error[] = new DangerText('Zeile: '.($RunY)).' Schüler '.$tblPerson->getLastFirstName()
                        .' wurde nicht hinzugefügt. "bereits vorhanden"';
                    continue;
                }

                $secondName = trim($Document->getValue($Document->getCell($Location['2ter_Vorname'], $RunY)));
                $callName = trim($Document->getValue($Document->getCell($Location['Rufname'], $RunY)));
                $Stammgruppe = trim($Document->getValue($Document->getCell($Location['Stammgruppe'], $RunY)));
                $Hort = trim($Document->getValue($Document->getCell($Location['Hort'], $RunY)));
                $tblPerson = $this->setPersonStudent($firstName, $secondName, $callName, $lastName, $Stammgruppe, $Hort);
                $countStudent++;

                // common & birthday
                $studentGender = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));
                $studentBirth = trim($Document->getValue($Document->getCell($Location['Geburtsdatum'], $RunY)));
                $birthPlace = trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY)));
                $nationality = trim($Document->getValue($Document->getCell($Location['Staatsangehörigkeit'], $RunY)));
                $denomination = ''; // ToDO Konfession nachpflegen?
                $remark = trim($Document->getValue($Document->getCell($Location['Abholberechtigte'], $RunY)));
                $this->setPersonBirth($tblPerson, $studentBirth, $birthPlace, $studentGender, $nationality, $denomination, $remark, $RunY, $error);

                // student
                $schoolAttendanceStartDate = trim($Document->getValue($Document->getCell($Location['Ersteinschlung_Datum'], $RunY)));
                // medicine
                $tblStudentMedicalRecord = null;
                $disease = trim($Document->getValue($Document->getCell($Location['Allergien'], $RunY)));
                $medication = trim($Document->getValue($Document->getCell($Location['Medikamente'], $RunY)));
                $insurance = trim($Document->getValue($Document->getCell($Location['Krankenkasse'], $RunY)));
                $this->setPersonTblStudent($tblPerson, $schoolAttendanceStartDate, $disease, $medication, $insurance, $RunY, $error);

                // division
                $divisionString = trim($Document->getValue($Document->getCell($Location['Klasse/Kurs'], $RunY)));
                $schoolType = trim($Document->getValue($Document->getCell($Location['Schulart'], $RunY)));
                $this->setPersonDivision($tblPerson, $YearString, $divisionString, $schoolType, $RunY, $error);

                // address
                $streetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                $streetNumber = trim($Document->getValue($Document->getCell($Location['HNR'], $RunY)));
                $city = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                $cityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                $district = trim($Document->getValue($Document->getCell($Location['Ortsteil'], $RunY)));
                $this->setPersonAddress($tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $RunY, $error);

                // contact
                $emergencyPhone = trim($Document->getValue($Document->getCell($Location['Notfall_Festnetz'], $RunY)));
                $emergencyMobile = trim($Document->getValue($Document->getCell($Location['Notfall_Mobil'], $RunY)));
                $privatePhone = trim($Document->getValue($Document->getCell($Location['Privat_Festnetz'], $RunY)));
                $privateMobile = trim($Document->getValue($Document->getCell($Location['Privat_Mobil'], $RunY)));
                $privateMail = trim($Document->getValue($Document->getCell($Location['E_Mail_Privat'], $RunY)));
                $this->setPersonContact($tblPerson, $emergencyPhone, $emergencyMobile, $privatePhone, $privateMobile, '', '', $privateMail, '');

                // S1 --------------------------------------------------------------------------------------------------
                $firstName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Vorname'], $RunY)));
                $lastName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Name'], $RunY)));
                $cityCode_S1 = trim($Document->getValue($Document->getCell($Location['S1_PLZ'], $RunY)));
                // nur vorhandene Datensätze
                if($firstName_S1 != '' && $lastName_S1 != ''){
                    $addInformation = true;
                    $tblPerson_S1 = Person::useService()->existsPerson($firstName_S1, $lastName_S1, $cityCode_S1);
                    if(!$tblPerson_S1)
                    {
                        $salutation_S1 = trim($Document->getValue($Document->getCell($Location['S1_Anrede'], $RunY)));
                        $title_S1 = trim($Document->getValue($Document->getCell($Location['S1_Titel'], $RunY)));
                        $memberNumber_S1 = trim($Document->getValue($Document->getCell($Location['S1_Mitgliedsnummer'], $RunY)));
                        $assistance_S1 = trim($Document->getValue($Document->getCell($Location['S1_Mitarbeitbereitschaft'], $RunY)));
                        $tblPerson_S1 = $this->setPersonCustody($salutation_S1, $title_S1, $firstName_S1, $lastName_S1, $memberNumber_S1, $assistance_S1);
                        $countS1++;
                    } else {
                        $info[] = new Muted(new Small('Zeile: '.($RunY + 1).' Der Sorgeberechtigte S1 wurde nicht angelegt, da schon eine 
                        Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                        Person verknüpft'));
                        $countS1Exists++;
                        // keine doppelte Datenpflege
                        $addInformation = false;
                    }
                    if($addInformation){
                        // custody
                        $occupation = trim($Document->getValue($Document->getCell($Location['S1_Beruf'], $RunY)));
                        $employment = trim($Document->getValue($Document->getCell($Location['S1_Arbeitsstelle'], $RunY)));
                        $remark = trim($Document->getValue($Document->getCell($Location['S1_Bemerkungen'], $RunY)));
                        Custody::useService()->insertMeta($tblPerson_S1, $occupation, $employment, $remark);

                        // S1 address
                        $streetName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Straße'], $RunY)));
                        $streetNumber_S1 = trim($Document->getValue($Document->getCell($Location['S1_HNR'], $RunY)));
                        $city_S1 = trim($Document->getValue($Document->getCell($Location['S1_Ort'], $RunY)));
                        $cityCode_S1 = trim($Document->getValue($Document->getCell($Location['S1_PLZ'], $RunY)));
                        $district_S1 = trim($Document->getValue($Document->getCell($Location['S1_Ortsteil'], $RunY)));
                        $this->setPersonAddress($tblPerson_S1, $streetName_S1, $streetNumber_S1, $city_S1, $cityCode_S1, $district_S1, $RunY, $error);

                        // S1 contact
                        $emergencyPhone_S1 = trim($Document->getValue($Document->getCell($Location['S1_Notfall_Festnetz'], $RunY)));
                        $emergencyMobile_S1 = trim($Document->getValue($Document->getCell($Location['S1_Notfall_Mobil'], $RunY)));
                        $privatePhone_S1 = trim($Document->getValue($Document->getCell($Location['S1_Privat_Festnetz'], $RunY)));
                        $privateMobile_S1 = trim($Document->getValue($Document->getCell($Location['S1_Privat_Mobil'], $RunY)));
                        $businessPhone_S1 = trim($Document->getValue($Document->getCell($Location['S1_Geschäftlich_Festnetz'], $RunY)));
                        $businessMobile_S1 = trim($Document->getValue($Document->getCell($Location['S1_Geschäftlich_Mobil'], $RunY)));
                        $privateMail_S1 = trim($Document->getValue($Document->getCell($Location['S1_E_Mail_Privat'], $RunY)));
                        $businessMail_S1 = trim($Document->getValue($Document->getCell($Location['S1_E_Mail_Geschäftlich'], $RunY)));
                        $this->setPersonContact($tblPerson_S1, $emergencyPhone_S1, $emergencyMobile_S1, $privatePhone_S1,
                            $privateMobile_S1, $businessPhone_S1, $businessMobile_S1, $privateMail_S1, $businessMail_S1);

                        // Billing
                        $bankName_S1 = trim($Document->getValue($Document->getCell($Location['S1_Bankname'], $RunY)));
                        $IBAN_S1 = trim($Document->getValue($Document->getCell($Location['S1_IBAN'], $RunY)));
                        $BIC_S1 = trim($Document->getValue($Document->getCell($Location['S1_BIC'], $RunY)));
                        // nur vollständige Daten importieren
                        if($bankName_S1 != '' && $IBAN_S1 != '' && $BIC_S1 != ''){
                            $this->setPersonBankAccount($tblPerson_S1, $bankName_S1, $IBAN_S1, $BIC_S1);
                        }
                    }

                    // relationship
                    $tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN);
                    Relationship::useService()->insertRelationshipToPerson($tblPerson_S1, $tblPerson, $tblRelationshipType, '', 1);
                }

                // S2 --------------------------------------------------------------------------------------------------
                $firstName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Vorname'], $RunY)));
                $lastName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Name'], $RunY)));
                $cityCode_S2 = trim($Document->getValue($Document->getCell($Location['S2_PLZ'], $RunY)));
                // nur vorhandene Datensätze
                if($firstName_S2 != '' && $lastName_S2 != ''){
                    $addInformation = true;
                    $tblPerson_S2 = Person::useService()->existsPerson($firstName_S2, $lastName_S2, $cityCode_S2);
                    if(!$tblPerson_S2)
                    {
                        $salutation_S2 = trim($Document->getValue($Document->getCell($Location['S2_Anrede'], $RunY)));
                        $title_S2 = trim($Document->getValue($Document->getCell($Location['S2_Titel'], $RunY)));
                        $memberNumber_S2 = trim($Document->getValue($Document->getCell($Location['S2_Mitgliedsnummer'], $RunY)));
                        $assistance_S2 = trim($Document->getValue($Document->getCell($Location['S2_Mitarbeitbereitschaft'], $RunY)));
                        $tblPerson_S2 = $this->setPersonCustody($salutation_S2, $title_S2, $firstName_S2, $lastName_S2, $memberNumber_S2, $assistance_S2);
                        $countS2++;
                    } else {
                        $info[] = new Muted(new Small('Zeile: '.($RunY + 1).' Der Sorgeberechtigte S2 wurde nicht angelegt, da schon eine 
                        Person mit gleichen Namen und gleicher PLZ existiert. Der Schüler wurde mit der bereits existierenden
                        Person verknüpft'));
                        $countS2Exists++;
                        // keine doppelte Datenpflege
                        $addInformation = false;
                    }
                    if($addInformation){
                        // custody
                        $occupation = trim($Document->getValue($Document->getCell($Location['S1_Beruf'], $RunY)));
                        $employment = trim($Document->getValue($Document->getCell($Location['S1_Arbeitsstelle'], $RunY)));
                        $remark = trim($Document->getValue($Document->getCell($Location['S1_Bemerkungen'], $RunY)));
                        Custody::useService()->insertMeta($tblPerson_S2, $occupation, $employment, $remark);
                        // S2 address
                        $streetName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Straße'], $RunY)));
                        $streetNumber_S2 = trim($Document->getValue($Document->getCell($Location['S2_HNR'], $RunY)));
                        $city_S2 = trim($Document->getValue($Document->getCell($Location['S2_Ort'], $RunY)));
                        $cityCode_S2 = trim($Document->getValue($Document->getCell($Location['S2_PLZ'], $RunY)));
                        $district_S2 = trim($Document->getValue($Document->getCell($Location['S2_Ortsteil'], $RunY)));
                        $this->setPersonAddress($tblPerson_S2, $streetName_S2, $streetNumber_S2, $city_S2, $cityCode_S2, $district_S2, $RunY, $error);

                        // S2 contact
                        $emergencyPhone_S2 = trim($Document->getValue($Document->getCell($Location['S2_Notfall_Festnetz'], $RunY)));
                        $emergencyMobile_S2 = trim($Document->getValue($Document->getCell($Location['S2_Notfall_Mobil'], $RunY)));
                        $privatePhone_S2 = trim($Document->getValue($Document->getCell($Location['S2_Privat_Festnetz'], $RunY)));
                        $privateMobile_S2 = trim($Document->getValue($Document->getCell($Location['S2_Privat_Mobil'], $RunY)));
                        $businessPhone_S2 = trim($Document->getValue($Document->getCell($Location['S2_Geschäftlich_Festnetz'], $RunY)));
                        $businessMobile_S2 = trim($Document->getValue($Document->getCell($Location['S2_Geschäftlich_Mobil'], $RunY)));
                        $privateMail_S2 = trim($Document->getValue($Document->getCell($Location['S2_E_Mail_Privat'], $RunY)));
                        $businessMail_S2 = trim($Document->getValue($Document->getCell($Location['S2_E_Mail_Geschäftlich'], $RunY)));
                        $this->setPersonContact($tblPerson_S2, $emergencyPhone_S2, $emergencyMobile_S2, $privatePhone_S2,
                            $privateMobile_S2, $businessPhone_S2, $businessMobile_S2, $privateMail_S2, $businessMail_S2);

                        // Billing
                        $bankName_S2 = trim($Document->getValue($Document->getCell($Location['S2_Bankname'], $RunY)));
                        $IBAN_S2 = trim($Document->getValue($Document->getCell($Location['S2_IBAN'], $RunY)));
                        $BIC_S2 = trim($Document->getValue($Document->getCell($Location['S2_BIC'], $RunY)));
                        // nur vollständige Daten importieren
                        if($bankName_S2 != '' && $IBAN_S2 != '' && $BIC_S2 != ''){
                            $this->setPersonBankAccount($tblPerson_S2, $bankName_S2, $IBAN_S2, $BIC_S2);
                        }
                    }

                    // relationship
                    $tblRelationshipType = Relationship::useService()->getTypeByName(TblTypeRelationship::IDENTIFIER_GUARDIAN);
                    Relationship::useService()->insertRelationshipToPerson($tblPerson_S2, $tblPerson, $tblRelationshipType, '', 2);
                }
            }

            $AccordionInfo = new Accordion();
            $AccordionInfo->addItem('Information - Vorhandene Personen', new Listing($info));

            return
            new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Es wurden '.$countStudent.' Schüler erfolgreich angelegt.', null, false, '25', '5')
                    , 4),
                    new LayoutColumn(
                        new Success('Es wurden '.$countS1.' Sorgeberechtigte S1 erfolgreich angelegt.'.
                        ($countS1Exists > 0
                            ? new Warning(' ('.$countS1Exists.' dopplungen) ', null, false, '1', '5')
                              .($countS1 + $countS1Exists).' Zuweisungen zu Schülern.'
                            : '')
                        , null, false, '3', '5')
                    , 4),
                    new LayoutColumn(
                        new Success('Es wurden '.$countS2.' Sorgeberechtigte S2 erfolgreich angelegt.'.
                        ($countS2Exists > 0
                            ? new Warning(' ('.$countS2Exists.' dopplungen) ', null, false, '1', '5')
                              .($countS2 + $countS2Exists).' Zuweisungen zu Schülern.'
                            : '')
                        , null, false, '3', '5')
                    , 4),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel(
                            'Fehler',
                            $error,
                            Panel::PANEL_TYPE_DANGER
                        )
                    ),
                    new LayoutColumn(
                        $AccordionInfo
                    )
                ))
            )));

        } else {
            return new Warning(json_encode($Location)).new Danger(
                    "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
        }
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null   $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename().'.'.$File->getClientOriginalExtension());

                /**
                 * Read
                 */
                //$File->getMimeType()
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());
                if (!$Document instanceof PhpExcel) {
                    $Form->setError('File', 'Fehler');
                    return $Form;
                }

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Termin'                => null,
                    'Name'                  => null,
                    'Kind'                  => null,
                    'Geb.- dat.'            => null,
                    'Adresse'               => null,
                    'PLZ'                   => null,
                    'Ort'                   => null,
                    'Name Mutter'           => null,
                    'Vorname Mutter'        => null,
                    'Name Vater'            => null,
                    'Vorname Vater'         => null,
                    'Telefon'               => null,
                    'Mail_1'                => null,
                    'Mail_2'                => null,
                    'Konf.'                 => null,
                    'Anm.-dat.'             => null,
                    'Grundschule'           => null,
                    'Bemerkungen'           => null,
                    'Zweitwunsch Gymnasium' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countInterestedPerson = 0;
                    $countFather = 0;
                    $countMother = 0;
                    $countFatherExists = 0;
                    $countMotherExists = 0;

                    $error = array();

                    for ($RunY = 1; $RunY < $Y; $RunY++) {

                        // InterestedPerson
                        $firstName = trim($Document->getValue($Document->getCell($Location['Kind'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));

                        if ($firstName !== '' && $lastName !== '') {
                            $tblPerson = Person::useService()->insertPerson(
                                Person::useService()->getSalutationById(3),    //Schüler
                                '',
                                $firstName,
                                '',
                                $lastName,
                                array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                    1 => Group::useService()->getGroupByMetaTable('PROSPECT')
                                )
                            );

                            if ($tblPerson !== false) {
                                $countInterestedPerson++;

                                $cityName = trim($Document->getValue($Document->getCell($Location['Ort'],
                                    $RunY)));
                                $cityCode = str_pad(
                                    trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY))),
                                    5,
                                    "0",
                                    STR_PAD_LEFT
                                );
                                $cityDistrict = '';
                                $pos = strpos($cityName, " OT ");
                                if ($pos !== false) {
                                    $cityDistrict = trim(substr($cityName, $pos + 4));
                                    $cityName = trim(substr($cityName, 0, $pos));
                                }
                                $StreetName = '';
                                $StreetNumber = '';
                                $Street = trim($Document->getValue($Document->getCell($Location['Adresse'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $Street, $matches)) {
                                    $pos = strpos($Street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $StreetName = trim(substr($Street, 0, $pos));
                                        $StreetNumber = trim(substr($Street, $pos));
                                    }
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Geb.- dat.'],
                                    $RunY)));
                                if ($day !== '') {
                                    $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $birthday = '';
                                }
                                $Denomination = trim($Document->getValue($Document->getCell($Location['Konf.'],
                                    $RunY)));

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    '',
                                    TblCommonBirthDates::VALUE_GENDER_NULL,
                                    '',
                                    $Denomination,
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                $remark = trim($Document->getValue($Document->getCell($Location['Bemerkungen'],
                                    $RunY)));
                                $info = trim($Document->getValue($Document->getCell($Location['Grundschule'], $RunY)));
                                if ($info !== '') {
                                    $remark .= ($remark == '' ? '' : " \n").'Grundschule: '.$info;
                                }
                                $info = trim($Document->getValue($Document->getCell($Location['Zweitwunsch Gymnasium'],
                                    $RunY)));
                                if ($info !== '') {
                                    $remark .= ($remark == '' ? '' : " \n").'Zweitwunsch: '.$info;
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Termin'],
                                    $RunY)));
                                if ($day !== '') {
                                    $interviewDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $interviewDate = '';
                                }

                                $day = trim($Document->getValue($Document->getCell($Location['Anm.-dat.'],
                                    $RunY)));
                                if ($day !== '') {
                                    $reservationDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                } else {
                                    $reservationDate = '';
                                }

                                Prospect::useService()->insertMeta(
                                    $tblPerson,
                                    $reservationDate,
                                    $interviewDate,
                                    '',
                                    '',
                                    '',
                                    null,
                                    null,
                                    $remark
                                );

                                $tblRelationshipTypeCustody = Relationship::useService()->getTypeById(1);

                                // Custody1
                                $tblPersonCustody1 = null;
                                $firstNameCustody1 = trim($Document->getValue($Document->getCell($Location['Vorname Mutter'],
                                    $RunY)));
                                $lastNameCustody1 = trim($Document->getValue($Document->getCell($Location['Name Mutter'],
                                    $RunY)));

                                if ($firstNameCustody1 !== '' && $lastNameCustody1 !== '') {
                                    $tblPersonCustody1Exists = Person::useService()->existsPerson(
                                        $firstNameCustody1,
                                        $lastNameCustody1,
                                        $cityCode
                                    );

                                    if (!$tblPersonCustody1Exists) {
                                        $tblPersonCustody1 = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $firstNameCustody1,
                                            '',
                                            $lastNameCustody1,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        // E-Mail
                                        $motherMail = trim($Document->getValue($Document->getCell($Location['Mail_1'],
                                            $RunY)));
                                        if ($motherMail != '' && $tblPersonCustody1) {
                                            $tblType = Mail::useService()->getTypeById(1);
                                            Mail::useService()->insertMailToPerson(
                                                $tblPersonCustody1,
                                                $motherMail,
                                                $tblType,
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        // Address
                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody1, $StreetName, $StreetNumber, $cityCode, $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Sorgeberechtigen1 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                        }

                                        $countFather++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody1Exists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countFatherExists++;
                                        $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
                                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                // Custody2
                                $tblPersonCustody2 = null;
                                $firstNameCustody2 = trim($Document->getValue($Document->getCell($Location['Vorname Vater'],
                                    $RunY)));
                                $lastNameCustody2 = trim($Document->getValue($Document->getCell($Location['Name Vater'],
                                    $RunY)));

                                if ($firstNameCustody2 !== '' && $lastNameCustody2 !== '') {
                                    $tblPersonCustody2Exists = Person::useService()->existsPerson(
                                        $firstNameCustody2,
                                        $lastNameCustody2,
                                        $cityCode
                                    );

                                    if (!$tblPersonCustody2Exists) {
                                        $tblPersonCustody2 = Person::useService()->insertPerson(
                                            null,
                                            '',
                                            $firstNameCustody2,
                                            '',
                                            $lastNameCustody2,
                                            array(
                                                0 => Group::useService()->getGroupByMetaTable('COMMON'),
                                                1 => Group::useService()->getGroupByMetaTable('CUSTODY')
                                            )
                                        );

                                        // E-Mail
                                        $fatherMail = trim($Document->getValue($Document->getCell($Location['Mail_2'],
                                            $RunY)));
                                        if ($fatherMail != '' && $firstNameCustody2) {
                                            $tblType = Mail::useService()->getTypeById(1);
                                            Mail::useService()->insertMailToPerson(
                                                $tblPersonCustody2,
                                                $fatherMail,
                                                $tblType,
                                                ''
                                            );
                                        }

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                            Address::useService()->insertAddressToPerson(
                                                $tblPersonCustody2, $StreetName, $StreetNumber, $cityCode, $cityName,
                                                $cityDistrict, ''
                                            );
                                        } else {
                                            $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Sorgeberechtigen2 wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                        }

                                        $countMother++;
                                    } else {

                                        Relationship::useService()->insertRelationshipToPerson(
                                            $tblPersonCustody2Exists,
                                            $tblPerson,
                                            $tblRelationshipTypeCustody,
                                            ''
                                        );

                                        $countMotherExists++;
                                        $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte wurde nicht angelegt, da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                    }
                                } else {
                                    $error[] = 'Zeile: '.($RunY + 1).' Der Sorgeberechtigte1 wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                                }

                                if ($StreetName && $StreetNumber && $cityCode && $cityName) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $StreetName, $StreetNumber, $cityCode, $cityName, $cityDistrict, ''
                                    );
                                } else {
                                    $error[] = 'Zeile: '.($RunY + 1).' Die Adresse des Interessenten wurde nicht angelegt, da sie keine vollständige Adresse besitzt.';
                                }

                                /*
                                * Phone
                                */
                                $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                    $RunY)));
                                if ($phoneNumber !== '') {
                                    $phoneNumberList = explode(',', $phoneNumber);
                                    foreach ($phoneNumberList as $phone) {
                                        $phone = trim($phone);
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phone, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phone,
                                            $tblType,
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: '.($RunY + 1).' Der Schüler wurde nicht hinzugefügt, da er keinen Vornamen und/oder Namen besitzt.';
                        }
                    }

                    return
                        new Success('Es wurden '.$countInterestedPerson.' Intessenten erfolgreich angelegt.').
                        new Success('Es wurden '.$countFather.' Sorgeberechtigte1 erfolgreich angelegt.').
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists.' Sorgeberechtigte1 exisistieren bereits.') : '').
                        new Success('Es wurden '.$countMother.' Sorgeberechtigte2 erfolgreich angelegt.').
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists.' Sorgeberechtigte2 exisistieren bereits.') : '')
                        .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));
                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile        $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStaffFromFile(IFormInterface $Form = null, UploadedFile $File = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $File) {
            return $Form;
        }

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(), $File->getFilename().'.'.$File->getClientOriginalExtension());
                /**
                 * Read
                 */
                /** @var PhpExcel $Document */
                $Document = Document::getDocument($File->getPathname());

                $X = $Document->getSheetColumnCount();
                $Y = $Document->getSheetRowCount();

                /**
                 * Header -> Location
                 */
                $Location = array(
                    'Name'          => null,
                    'Vorname'       => null,
                    'Straße'        => null,
                    'PLZ, Ort'      => null,
                    'Telefon'       => null,
                    'Telefon mobil' => null,
                    'E-Mail'        => null,
                    'Konf.'         => null,
                    'Fächer (EGT)'  => null,
                    'Geburtstag'    => null,
                    'Team'          => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = trim($Document->getValue($Document->getCell($RunX, 0)));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countStaff = 0;
                    $countStaffExists = 0;
                    $error = array();

                    $tblStaffGroup = Group::useService()->getGroupByMetaTable('STAFF');
                    $tblTeacherGroup = Group::useService()->getGroupByMetaTable('TEACHER');

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                        if ($firstName !== '' && $lastName !== '') {

                            $city = trim($Document->getValue($Document->getCell($Location['PLZ, Ort'], $RunY)));
                            $cityName = '';
                            $cityCode = '';
                            if (preg_match('!(\d+)\s(\w+)!', $city, $matches)) {

                                if (isset($matches[1])) {
                                    $cityCode = trim($matches[1]);
                                }
                                if (isset($matches[2])) {
                                    $cityName = trim($matches[2]);
                                }
                            }

                            $tblPersonExists = Person::useService()->existsPerson(
                                $firstName,
                                $lastName,
                                $cityCode
                            );
                            $SubjectEGT = trim($Document->getValue($Document->getCell($Location['Fächer (EGT)'],
                                $RunY)));
                            if ($tblPersonExists) {

                                $error[] = 'Zeile: '.($RunY + 1).' Die Person wurde nicht angelegt, 
                                da schon eine Person mit gleichen Namen und gleicher PLZ existiert.';
                                Group::useService()->addGroupPerson($tblStaffGroup, $tblPersonExists);
                                if ($SubjectEGT !== '') {
                                    Group::useService()->addGroupPerson($tblTeacherGroup, $tblPersonExists);
                                }
                                $countStaffExists++;
                            } else {
                                if ($SubjectEGT != '') {
                                    $tblPerson = Person::useService()->insertPerson(
                                        null,
                                        '',
                                        $firstName,
                                        '',
                                        $lastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON')->getId(),
                                            1 => $tblStaffGroup->getId(),
                                            2 => $tblTeacherGroup->getId()
                                        )
                                    );
                                } else {
                                    $tblPerson = Person::useService()->insertPerson(
                                        null,
                                        '',
                                        $firstName,
                                        '',
                                        $lastName,
                                        array(
                                            0 => Group::useService()->getGroupByMetaTable('COMMON')->getId(),
                                            1 => $tblStaffGroup->getId()
                                        )
                                    );
                                }

                                if ($tblPerson !== false) {
                                    $countStaff++;

                                    $day = trim($Document->getValue($Document->getCell($Location['Geburtstag'],
                                        $RunY)));
                                    if ($day !== '') {
                                        $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($day));
                                    } else {
                                        $birthday = '';
                                    }
                                    $denomination = trim($Document->getValue($Document->getCell($Location['Konf.'],
                                        $RunY)));

                                    $remark = '';
                                    $info = trim($Document->getValue($Document->getCell($Location['Fächer (EGT)'],
                                        $RunY)));
                                    if ($info !== '') {
                                        $remark = 'Fächer (EGT): '.$info;
                                    }
                                    $info = trim($Document->getValue($Document->getCell($Location['Team'], $RunY)));
                                    if ($info !== '') {
                                        $remark .= ($remark == '' ? '' : "\n").'Team: '.$info;
                                    }

                                    Common::useService()->insertMeta(
                                        $tblPerson,
                                        $birthday,
                                        '',
                                        TblCommonBirthDates::VALUE_GENDER_NULL,
                                        '',
                                        $denomination,
                                        TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                        '',
                                        $remark
                                    );

                                    // Address
                                    $streetName = '';
                                    $streetNumber = '';
                                    $street = trim($Document->getValue($Document->getCell($Location['Straße'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $street, $matches)) {
                                        $pos = strpos($street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $streetName = trim(substr($street, 0, $pos));
                                            $streetNumber = trim(substr($street, $pos));
                                        }
                                    }
                                    if ($streetName && $streetNumber && $cityCode && $cityName) {
                                        Address::useService()->insertAddressToPerson(
                                            $tblPerson, $streetName, $streetNumber, $cityCode, $cityName, '', ''
                                        );
                                    } else {
                                        $error[] = 'Zeile: '.($RunY + 1).' Die Adresse der Person wurde nicht angelegt, 
                                        da sie keine vollständige Adresse besitzt.';
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }

                                    $phoneNumber = trim($Document->getValue($Document->getCell($Location['Telefon mobil'],
                                        $RunY)));
                                    if ($phoneNumber != '') {
                                        $tblType = Phone::useService()->getTypeById(1);
                                        if (0 === strpos($phoneNumber, '01')) {
                                            $tblType = Phone::useService()->getTypeById(2);
                                        }
                                        Phone::useService()->insertPhoneToPerson(
                                            $tblPerson,
                                            $phoneNumber,
                                            $tblType,
                                            ''
                                        );
                                    }

                                    $mailAddress = trim($Document->getValue($Document->getCell($Location['E-Mail'],
                                        $RunY)));
                                    if ($mailAddress != '') {
                                        Mail::useService()->insertMailToPerson(
                                            $tblPerson,
                                            $mailAddress,
                                            Mail::useService()->getTypeById(1),
                                            ''
                                        );
                                    }
                                }
                            }
                        } else {
                            $error[] = 'Zeile: '.($RunY + 1).' Die Person wurde nicht angelegt, da sie keinen Namen und Vornamen hat.';
                        }
                    }

                    Debugger::screenDump($error);

                    return
                        new Success('Es wurden '.$countStaff.' Mitarbeiter erfolgreich angelegt.').
                        ($countStaffExists > 0 ?
                            new Warning($countStaffExists.' Mitarbeiter exisistieren bereits.') : '')
                        .(empty($error) ? '' : new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                                new Panel(
                                    'Fehler',
                                    $error,
                                    Panel::PANEL_TYPE_DANGER
                                ))
                        ))));

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)).new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param string $firstName
     * @param string $secondName
     * @param string $callName
     * @param string $lastName
     * @param string $Stammgruppe
     * @param string $Hort
     *
     * @return bool|TblPerson
     */
    private function setPersonStudent($firstName, $secondName, $callName, $lastName, $Stammgruppe, $Hort)
    {

        // Auswahl der Stammgruppe
        $tblGroupS = false;
        if(!$Stammgruppe){
            $tblGroupS = Group::useService()->createGroupFromImport($Stammgruppe, 'Stammgruppe');
        }
        $GroupList = array();
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        if($tblGroupS){
            $GroupList[] = $tblGroupS;
        }
        if($Hort != ''){
            $tblGroupH = Group::useService()->createGroupFromImport('Hort');
            $GroupList[] = $tblGroupH;
        }

        return Person::useService()->insertPerson(
            Person::useService()->getSalutationByName(TblSalutation::VALUE_STUDENT),
            '',
            $firstName,
            $secondName,
            $lastName,
            $GroupList,
            '',
            '',
            $callName
        );
    }

    /**
     * @param string $salutation
     * @param string $title
     * @param string $firstName
     * @param string $lastName
     * @param string $memberNumber
     * @param string $assistance
     *
     * @return bool|TblPerson
     */
    private function setPersonCustody($salutation, $title, $firstName, $lastName, $memberNumber, $assistance)
    {

        $GroupList = array();
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_COMMON);
        $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        if($memberNumber){
            $GroupList[] = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB);
        }

        $tblSalutation = false;
        $gender = 0;
        if($salutation){
            $salutation = strtolower($salutation);
            switch($salutation){
                case 'herr':
                case 'm':
                case 'h':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_MAN);
                    $gender = TblCommonGender::VALUE_MALE;
                break;
                case 'frau':
                case 'w':
                case 'f':
                    $tblSalutation = Person::useService()->getSalutationByName(TblSalutation::VALUE_WOMAN);
                    $gender = TblCommonGender::VALUE_FEMALE;
                break;
            }
        }

        $tblPerson = Person::useService()->insertPerson(
            $tblSalutation,
            $title,
            $firstName,
            '',
            $lastName,
            $GroupList
        );

        $isAssistance = TblCommonInformation::VALUE_IS_ASSISTANCE_NULL;
        if($assistance){
            $isAssistance = TblCommonInformation::VALUE_IS_ASSISTANCE_YES;
        }

        Common::useService()->insertMeta(
            $tblPerson,
            '',
            '',
            $gender,
            '',
            '',
            $isAssistance,
            $assistance,
            ''
        );

        Club::useService()->insertMeta($tblPerson, $memberNumber);

        return $tblPerson;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $birthdayString
     * @param string    $birthPlace
     * @param string    $gender
     * @param string    $nationality
     * @param string    $denomination
     * @param string    $remark
     * @param int       $RunY
     * @param array     $error
     */
    private function setPersonBirth(TblPerson $tblPerson, $birthdayString, $birthPlace, $gender, $nationality, $denomination, $remark, $RunY, &$error)
    {

        if ($birthdayString !== '') {
            try {
                $birthday = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($birthdayString));
            } catch (\Exception $ex) {
                $birthday = '';
                $error[] = 'Zeile: '.($RunY + 1).' Ungültiges Geburtsdatum: '.$ex->getMessage();
            }
        } else {
            $birthday = '';
        }
        if($gender != ''){
            $gender = strtolower($gender);
            switch ($gender){
                case 'm':
                case 'männlich':
                case 'mann':
                    $gender = TblCommonGender::VALUE_MALE;
                break;
                case 'w':
                case 'weiblich':
                case 'frau':
                    $gender = TblCommonGender::VALUE_FEMALE;
                break;
                default:
                    // Geschlecht nicht zuweisbar
                    $gender = '';
            }
        }

        Common::useService()->insertMeta(
            $tblPerson,
            $birthday,
            $birthPlace,
            $gender,
            $nationality,
            $denomination,
            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
            '',
            $remark
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $YearString
     * @param string    $divisionString
     * @param string    $schoolType
     * @param int       $RunY
     * @param array     $error
     *
     * @throws \Exception
     */
    private function setPersonDivision(TblPerson $tblPerson, $YearString, $divisionString, $schoolType, $RunY, &$error)
    {

        $year = (int)$YearString;
        $yearShort = (int)substr($YearString, 2, 2);

        $tblDivision = false;
        if ($divisionString !== '') {
            $tblYear = Term::useService()->insertYear($year.'/'.($yearShort + 1));
            if ($tblYear) {
                $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                if (!$tblPeriodList) {
                    // firstTerm
                    $tblPeriod = Term::useService()->insertPeriod(
                        '1. Halbjahr',
                        '01.08.'.$year,
                        '31.01.'.($year + 1)
                    );
                    if ($tblPeriod) {
                        Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                    }

                    // secondTerm
                    $tblPeriod = Term::useService()->insertPeriod(
                        '2. Halbjahr',
                        '01.02.'.($year + 1),
                        '31.07.'.($year + 1)
                    );
                    if ($tblPeriod) {
                        Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                    }
                }

                if (strlen($divisionString) > 1) {
                    if (is_numeric(substr($divisionString, 0, 2))) {
                        $pos = 2;
                        $level = substr($divisionString, 0, $pos);
                        // remove the "-"
                        if (substr($divisionString, $pos, 1) == '-') {
                            $pos = 3;
                            $division = trim(substr($divisionString, $pos));
                        } else {
                            $division = trim(substr($divisionString, $pos));
                        }
                    } else {
                        $pos = 1;
                        $level = substr($divisionString, 0, $pos);
                        $division = trim(substr($divisionString, $pos));
                    }
                } else {
                    $level = $divisionString;
                    $division = '';
                }

                $schoolType = strtolower($schoolType);
                switch($schoolType){
                    case 'gs':
                    case 'grundschule':
                        $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GRUND_SCHULE);
                    break;
                    case 'ms':
                    case 'os':
                    case 'mittelschule':
                    case 'oberschule':
                    case 'mittelschule/oberschule':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_OBER_SCHULE);
                    break;
                    case 'gym':
                    case 'gymnasium':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_GYMNASIUM);
                    break;
                    case 'bs':
                    case 'berufsschule':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_SCHULE);
                    break;
                    case 'bfs':
                    case 'berufsfachschule':
                    $tblSchoolType = Type::useService()->getTypeByName(TblType::IDENT_BERUFS_FACH_SCHULE);
                    break;
                    default:
                        $tblSchoolType = false;
                }

                if($tblSchoolType){
                    $tblLevel = Division::useService()->insertLevel($tblSchoolType, $level);
                    if ($tblLevel) {
                        $tblDivision = Division::useService()->insertDivision(
                            $tblYear,
                            $tblLevel,
                            $division
                        );
                    }
                }
            }

            if ($tblDivision) {
                Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);
            } else {
                if($tblSchoolType){
                    $error[] = new DangerText('Zeile: '.($RunY + 1)).' Der Schüler konnte keiner Klasse zugeordnet werden.';
                } else {
                    $error[] = new DangerText('Zeile: '.($RunY + 1)).' Die Schulart ist nicht verwendbar. Schüler keiner
                    Klasse zugeordnet.';
                }

            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $streetName
     * @param string    $streetNumber
     * @param string    $city
     * @param string    $cityCode
     * @param string    $district
     * @param int       $RunY
     * @param array     $error
     */
    private function setPersonAddress(TblPerson $tblPerson, $streetName, $streetNumber, $city, $cityCode, $district, $RunY, &$error)
    {

        if($district == ''){
            $cityTemp = $city;
            if (preg_match('!(\w*\s)(OT\s\w*)!is', $cityTemp, $found)) {
                $city = $found[1];
                $district = $found[2];
            }
        }

        if($streetNumber == ''){
            $street = $streetName;
            if (preg_match_all('!\d+!', $street, $matches)) {
                $pos = strpos($street, $matches[0][0]);
                if ($pos !== null) {
                    $streetName = trim(substr($street, 0, $pos));
                    $streetNumber = trim(substr($street, $pos));
                }
            }
        }


        if ($streetName !== '' && $streetNumber !== '' && $cityCode && $city
        ) {
                Address::useService()->insertAddressToPerson(
                    $tblPerson, $streetName, $streetNumber, $cityCode, $city,
                    $district, '', '', '', null
                );
        } else {
            $error[] = new DangerText('Zeile: '.($RunY + 1)).' '.$tblPerson->getLastFirstName().' Adresse konnte nicht angelegt werden.';
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $emergencyPhone
     * @param string    $emergencyMobile
     * @param string    $privatePhone
     * @param string    $privateMobile
     * @param string    $businessPhone
     * @param string    $businessMobile
     * @param string    $privateMail
     * @param string    $businessMail
     */
    private function setPersonContact(TblPerson $tblPerson, $emergencyPhone = '', $emergencyMobile = '',
        $privatePhone = '', $privateMobile = '', $businessPhone = '', $businessMobile = '', $privateMail = ''
        , $businessMail = '')
    {

        // phone/mobile
        if($emergencyPhone){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_EMERCENCY, TblTypePhone::VALUE_DESCRIPTION_PHONE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $emergencyPhone, $tblType, '');
        }
        if($emergencyMobile){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_EMERCENCY, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $emergencyMobile, $tblType, '');
        }
        if($privatePhone){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_PHONE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $privatePhone, $tblType, '');
        }
        if($privateMobile){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_PRIVATE, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $privateMobile, $tblType, '');
        }
        if($businessPhone){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_BUSINESS, TblTypePhone::VALUE_DESCRIPTION_PHONE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $businessPhone, $tblType, '');
        }
        if($businessMobile){
            $tblType = Phone::useService()->getTypeByNameAndDescription(TblTypePhone::VALUE_NAME_BUSINESS, TblTypePhone::VALUE_DESCRIPTION_MOBILE);
            Phone::useService()->insertPhoneToPerson($tblPerson, $businessMobile, $tblType, '');
        }

        // mail
        if($privateMail){
            $tblType = Mail::useService()->getTypeById(1); // private
            Mail::useService()->insertMailToPerson($tblPerson, $privateMail, $tblType, '');
        }
        if($businessMail){
            $tblType = Mail::useService()->getTypeById(2); // business
            Mail::useService()->insertMailToPerson($tblPerson, $businessMail, $tblType, '');
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $bankName
     * @param string    $IBAN
     * @param string    $BIC
     */
    private function setPersonBankAccount(TblPerson $tblPerson, $bankName, $IBAN, $BIC)
    {

        $Owner = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
        Debtor::useService()->createBankAccount($tblPerson, $Owner, $bankName, $IBAN, $BIC);
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_DEBTOR);
        Group::useService()->addGroupPerson($tblGroup, $tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $schoolAttendanceStartDate
     * @param string    $disease
     * @param string    $medication
     * @param string    $insurance
     * @param int       $RunY
     * @param array     $error
     */
    private function setPersonTblStudent(TblPerson $tblPerson, $schoolAttendanceStartDate, $disease, $medication, $insurance, $RunY, &$error)
    {

        if ($schoolAttendanceStartDate !== '') {
            try {
                $schoolAttendanceStartDate = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($schoolAttendanceStartDate));
            } catch (\Exception $ex) {
                $schoolAttendanceStartDate = '';
                $error[] = new DangerText('Zeile: '.($RunY + 1)).' Ungültiges Einschulungsdatum: '.$ex->getMessage();
            }
        } else {
            $schoolAttendanceStartDate = '';
        }

        $tblStudentMedicalRecord = null;
        if($disease != '' || $medication != '' || $insurance != ''){
            $tblStudentMedicalRecord = Student::useService()->insertStudentMedicalRecord($disease, $medication, $insurance);
        }
        // Student
        Student::useService()->insertStudent($tblPerson, '', $tblStudentMedicalRecord, null, null, null, null, null, $schoolAttendanceStartDate);
    }
}