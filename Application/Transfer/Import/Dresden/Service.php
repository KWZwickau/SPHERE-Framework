<?php
namespace SPHERE\Application\Transfer\Import\Dresden;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as GroupCorporation;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createPersonFromFile(
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
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

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
                    'Nr' => null,       //
                    'FamZugeh' => null, //
                    'Titel' => null,    //
                    'Vorname' => null,  //
                    'Nachname' => null, //
                    'FamStand' => null, //
                    'FamStatus' => null,//
                    'Beruf' => null,    //
                    'EMailDi' => null,  //
                    'EMailPr' => null,  //
                    'GebDatum' => null, //
                    'Geburtsname' => null,  //
                    'Geburtsort' => null,   //
                    'Geschlecht' => null,   //
                    'Konfession' => null,   //
                    'MobiltelDi' => null,   //
                    'MobiltelPr' => null,   //
                    'NameZusatz' => null,   //
                    'Ort' => null,      //
                    'PLZ' => null,      //
                    'Strasse' => null,  //
//                    'Taufdatum' => null, // nur NULL Werte
                    'TelefaxDi' => null,//
                    'TelefaxPr' => null,//
                    'TelefonDi' => null,//
                    'TelefonPr' => null,//
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
                    $countPerson = 0;

                    $RelationshipList = array();
                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        if ($firstName === '' || $lastName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person wurde nicht hinzugefügt, da diese keinen Vornamen und/oder Namen besitzt.';
                        } else {
                            // Männlich oder Weiblich
                            $Geschlecht = trim($Document->getValue($Document->getCell($Location['Geschlecht'], $RunY)));

                            // Welche Person ist es?
                            $FamilyStatus = trim($Document->getValue($Document->getCell($Location['FamStatus'], $RunY)));
                            $isCustody = false;
                            if($FamilyStatus === 'Kind'){
                                $tblSalutation = Person::useService()->getSalutationById(3); //Schüler
                                $TblGroupList = array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
//                                    1 => Group::useService()->getGroupByMetaTable('STUDENT'),
                                );
                            } else {
                                if($Geschlecht == 'm'){
                                    $tblSalutation = Person::useService()->getSalutationById(1);
                                }elseif($Geschlecht == 'w'){
                                    $tblSalutation = Person::useService()->getSalutationById(2);
                                }else {
                                    $tblSalutation = null;
                                }
                                $isCustody = true;
                                $TblGroupList = array(
                                    0 => Group::useService()->getGroupByMetaTable('COMMON'),
//                                    1 => Group::useService()->getGroupByMetaTable('CUSTODY'),
                                );
                            }

                            $Title = trim($Document->getValue($Document->getCell($Location['Titel'], $RunY)));
                            $BirthName = trim($Document->getValue($Document->getCell($Location['Geburtsname'], $RunY)));

                            // zusätzlicher Namenzusatz
                            $NamensZusatz = trim($Document->getValue($Document->getCell($Location['NameZusatz'], $RunY)));
                            if($NamensZusatz != ''){
                                $NamensZusatz = 'Namenszussatz: '.$NamensZusatz;
                            }

                            $tblPerson = Person::useService()->insertPerson(
                                $tblSalutation,
                                $Title,
                                $firstName,
                                $NamensZusatz,
                                $lastName,
                                $TblGroupList,
                                $BirthName,
                                trim($Document->getValue($Document->getCell($Location['Nr'], $RunY)))
                            );

                            if ($tblPerson === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Person konnte nicht angelegt werden.';
                            } else {
                                $countPerson++;
                                if($isCustody){
                                    $Occupation = trim($Document->getValue($Document->getCell($Location['Beruf'], $RunY)));
                                    Custody::useService()->insertMeta($tblPerson, $Occupation, '', '');
                                }

                                if ($Geschlecht == 'm') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_MALE;
                                } elseif ($Geschlecht == 'w') {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_FEMALE;
                                } else {
                                    $gender = TblCommonBirthDates::VALUE_GENDER_NULL;
                                }

                                $birthday = trim($Document->getValue($Document->getCell($Location['GebDatum'],
                                    $RunY)));

                                Common::useService()->insertMeta(
                                    $tblPerson,
                                    $birthday,
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'],
                                        $RunY))),
                                    $gender,
                                    '',
                                    trim($Document->getValue($Document->getCell($Location['Konfession'],
                                        $RunY))),
                                    TblCommonInformation::VALUE_IS_ASSISTANCE_NULL,
                                    '',
                                    ''
                                );

                                // Address
                                $CityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                                $CityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Strasse'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $CityCode && $CityName
                                ) {
                                    Address::useService()->insertAddressToPerson(
                                        $tblPerson, $streetName, $streetNumber, $CityCode, $CityName, '', '');
                                }

                                // Telefon
                                $MobileDi = trim($Document->getValue($Document->getCell($Location['MobiltelDi'], $RunY)));
                                if($MobileDi){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Geschäftlich', 'Mobil');
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $MobileDi,
                                        $tblType,
                                        ''
                                    );
                                }
                                $MobilePr = trim($Document->getValue($Document->getCell($Location['MobiltelPr'], $RunY)));
                                if($MobilePr){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Privat', 'Mobil');
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $MobilePr,
                                        $tblType,
                                        ''
                                    );
                                }
                                $PhoneDi = trim($Document->getValue($Document->getCell($Location['TelefonDi'], $RunY)));
                                if($PhoneDi){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Geschäftlich', 'Festnetz');
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $PhoneDi,
                                        $tblType,
                                        ''
                                    );
                                }
                                $PhonePr = trim($Document->getValue($Document->getCell($Location['TelefonPr'], $RunY)));
                                if($PhonePr){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Privat', 'Festnetz');
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $PhonePr,
                                        $tblType,
                                        ''
                                    );
                                }
                                $FaxDi = trim($Document->getValue($Document->getCell($Location['TelefaxDi'], $RunY)));
                                if($FaxDi){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Fax', 'Geschäftlich');
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $FaxDi,
                                        $tblType,
                                        ''
                                    );
                                }
                                $FaxPr = trim($Document->getValue($Document->getCell($Location['TelefaxPr'], $RunY)));
                                if($FaxPr){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Fax', 'Privat');
                                    Phone::useService()->insertPhoneToPerson(
                                        $tblPerson,
                                        $FaxPr,
                                        $tblType,
                                        ''
                                    );
                                }

                                // E-Mail
                                $MailPr = trim($Document->getValue($Document->getCell($Location['EMailPr'], $RunY)));
                                if ($MailPr) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $MailPr,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }
                                $MailDi = trim($Document->getValue($Document->getCell($Location['EMailDi'], $RunY)));
                                if ($MailDi) {
                                    Mail::useService()->insertMailToPerson(
                                        $tblPerson,
                                        $MailDi,
                                        Mail::useService()->getTypeById(2),
                                        ''
                                    );
                                }

                                $Reference = trim($Document->getValue($Document->getCell($Location['FamZugeh'], $RunY)));
                                $Nr = trim($Document->getValue($Document->getCell($Location['Nr'], $RunY)));
                                if($Reference == 0){
                                    $RelationshipList[$Nr][$Nr] = $FamilyStatus;
                                } else {
                                    $RelationshipList[$Reference][$Nr] = $FamilyStatus;
                                }

                            }
                        }
                    }

//                    Debugger::screenDump($error);
//                    Debugger::screenDump($RelationshipList);

                    //DoRelationship
                    $tblRelationShipType = Relationship::useService()->getTypeByName('Sorgeberechtigt');
                    $tblRelationShipTypeMarried = Relationship::useService()->getTypeByName('Ehepartner');
                    if(!empty($RelationshipList)){
                        foreach($RelationshipList as $PeopleMainId => $PeopleList){
                            $tblMainPerson = Person::useService()->getPersonByImportId($PeopleMainId);
                            if($tblMainPerson){
                                foreach($PeopleList as $PeopleId => $Key){
                                    if(($tblPersonPeople = Person::useService()->getPersonByImportId($PeopleId))){
                                        if($Key == 'Kind'){
                                            // Kinder zur Hauptperson
                                            Relationship::useService()->insertRelationshipToPerson($tblMainPerson, $tblPersonPeople, $tblRelationShipType, '');
                                        } elseif($Key != 'Haupt') {
                                            // Verheiratet
                                            if($Key == 'Ehepartner'){
                                                Relationship::useService()->insertRelationshipToPerson($tblMainPerson, $tblPersonPeople, $tblRelationShipTypeMarried, '');
                                            }
                                            // Sorgebrechtigte die nicht die Hauptperson sind
                                            foreach ($PeopleList as $ChildId  => $Status) {
                                                if ($Status == 'Kind') {
                                                    // Kinder zur anderen Personen hinzufügen (Sorgerecht)
                                                    if (($tblPersonChild = Person::useService()->getPersonByImportId($ChildId))) {
                                                        Relationship::useService()->insertRelationshipToPerson($tblPersonPeople,
                                                            $tblPersonChild, $tblRelationShipType, '');
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countPerson . ' Personen erfolgreich angelegt.')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    Debugger::screenDump($Location);

                    return new Warning(json_encode($Location)) . new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param array  $resultList
     * @param string $groupString
     *
     * @return array
     */
    private function changeGroupArrayByGroupString($resultList = array(), $groupString = '')
    {
        // remove special char "$"
        $groupString = str_replace('$', '', $groupString);
        if(strpos($groupString,'#')) {
            // create list by #
            $resultList = array_merge($resultList, explode('#', $groupString));
        } else {
            // set list to one entry
            $resultList[] = $groupString;
        }
        // remove duplicates
        $resultList = array_unique($resultList);
        sort($resultList);
        return $resultList;
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInstitutionFromFile(
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
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

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
                    'Vorname' => null,  //
                    'Nachname' => null, //
                    'NameZusatz' => null,   //
                    'Strasse' => null,  //
                    'PLZ' => null,      //
                    'Ort' => null,      //
                    'EMailDi' => null,  //
                    'EMailPr' => null,  //
                    'TelefaxDi' => null,//
                    'TelefaxPr' => null,//
                    'TelefonDi' => null,//
                    'TelefonPr' => null,//
                    'MobiltelDi' => null,   //
                    'MobiltelPr' => null,   //
                    'InternetPrivat' => null,   //
                    'InternetDienstlich' => null,   //
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
                    $countCompany = 0;

                    $RelationshipList = array();
                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $InstitutionName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        $InstitutionExtendetName = trim($Document->getValue($Document->getCell($Location['NameZusatz'], $RunY)));
                        $InstitutionRemark = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        if ($InstitutionName === '') {
                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Die Institution wurde nicht hinzugefügt, da diese keinen Namen besitzt.';
                        } else {

                            $tblCompany = Company::useService()->insertCompany(
                                $InstitutionName,
                                $InstitutionRemark,
                                $InstitutionExtendetName
                            );

                            $tblGroup = GroupCorporation::useService()->getGroupByMetaTable('COMMON');
                            GroupCorporation::useService()->addGroupCompany($tblGroup, $tblCompany);

                            if ($tblCompany === false) {
                                $error[] = 'Zeile: ' . ($RunY + 1) . new DangerText(' Die Institution '.$InstitutionName.' konnte nicht angelegt werden.');
                            } else {
                                $countCompany++;

                                $CityCode = trim($Document->getValue($Document->getCell($Location['PLZ'], $RunY)));
                                $CityName = trim($Document->getValue($Document->getCell($Location['Ort'], $RunY)));
                                $streetName = '';
                                $streetNumber = '';
                                $street = trim($Document->getValue($Document->getCell($Location['Strasse'],
                                    $RunY)));
                                if (preg_match_all('!\d+!', $street, $matches)) {
                                    $pos = strpos($street, $matches[0][0]);
                                    if ($pos !== null) {
                                        $streetName = trim(substr($street, 0, $pos));
                                        $streetNumber = trim(substr($street, $pos));
                                    }
                                }
                                if ($streetName !== '' && $streetNumber !== ''
                                    && $CityCode && $CityName
                                ) {
                                    Address::useService()->insertAddressToCompany(
                                        $tblCompany, $streetName, $streetNumber, $CityCode, $CityName, '', '');
                                }

                                // Telefon
                                $MobileDi = trim($Document->getValue($Document->getCell($Location['MobiltelDi'], $RunY)));
                                if($MobileDi){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Geschäftlich', 'Mobil');
                                    Phone::useService()->insertPhoneToCompany(
                                        $tblCompany,
                                        $MobileDi,
                                        $tblType,
                                        ''
                                    );
                                }
                                $MobilePr = trim($Document->getValue($Document->getCell($Location['MobiltelPr'], $RunY)));
                                if($MobilePr){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Privat', 'Mobil');
                                    Phone::useService()->insertPhoneToCompany(
                                        $tblCompany,
                                        $MobilePr,
                                        $tblType,
                                        ''
                                    );
                                }
                                $PhoneDi = trim($Document->getValue($Document->getCell($Location['TelefonDi'], $RunY)));
                                if($PhoneDi){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Geschäftlich', 'Festnetz');
                                    Phone::useService()->insertPhoneToCompany(
                                        $tblCompany,
                                        $PhoneDi,
                                        $tblType,
                                        ''
                                    );
                                }
                                $PhonePr = trim($Document->getValue($Document->getCell($Location['TelefonPr'], $RunY)));
                                if($PhonePr){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Privat', 'Festnetz');
                                    Phone::useService()->insertPhoneToCompany(
                                        $tblCompany,
                                        $PhonePr,
                                        $tblType,
                                        ''
                                    );
                                }
                                $FaxDi = trim($Document->getValue($Document->getCell($Location['TelefaxDi'], $RunY)));
                                if($FaxDi){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Fax', 'Geschäftlich');
                                    Phone::useService()->insertPhoneToCompany(
                                        $tblCompany,
                                        $FaxDi,
                                        $tblType,
                                        ''
                                    );
                                }
                                $FaxPr = trim($Document->getValue($Document->getCell($Location['TelefaxPr'], $RunY)));
                                if($FaxPr){
                                    $tblType = Phone::useService()->getTypeByNameAndDescription('Fax', 'Privat');
                                    Phone::useService()->insertPhoneToCompany(
                                        $tblCompany,
                                        $FaxPr,
                                        $tblType,
                                        ''
                                    );
                                }

                                // E-Mail
                                $MailPr = trim($Document->getValue($Document->getCell($Location['EMailPr'], $RunY)));
                                if ($MailPr) {
                                    Mail::useService()->insertMailToCompany(
                                        $tblCompany,
                                        $MailPr,
                                        Mail::useService()->getTypeById(1),
                                        ''
                                    );
                                }
                                $MailDi = trim($Document->getValue($Document->getCell($Location['EMailDi'], $RunY)));
                                if ($MailDi) {
                                    Mail::useService()->insertMailToCompany(
                                        $tblCompany,
                                        $MailDi,
                                        Mail::useService()->getTypeById(2),
                                        ''
                                    );
                                }

                                // Internet
                                $WebPr = trim($Document->getValue($Document->getCell($Location['InternetPrivat'], $RunY)));
                                if ($WebPr) {
                                    Web::useService()->insertWebToCompany(
                                        $tblCompany,
                                        $WebPr,
                                        Web::useService()->getTypeById(1),
                                        ''
                                    );
                                }
                                $WebDi = trim($Document->getValue($Document->getCell($Location['InternetDienstlich'], $RunY)));
                                if ($WebDi) {
                                    Web::useService()->insertWebToCompany(
                                        $tblCompany,
                                        $WebDi,
                                        Web::useService()->getTypeById(2),
                                        ''
                                    );
                                }
                            }
                        }
                    }

//                    Debugger::screenDump($error);
//                    Debugger::screenDump($RelationshipList);

                    return
                        new Success('Es wurden ' . $countCompany . ' Institutionen erfolgreich angelegt.')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    Debugger::screenDump($Location);
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     *
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function updatePersonGroupFromFile(
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

                /** get GroupList definition */
                $DresdenGroup = new DresdenGroup();
                $GroupMatchList = $DresdenGroup->getGroupList();

                /**
                 * Prepare
                 */
                $File = $File->move($File->getPath(),
                    $File->getFilename() . '.' . $File->getClientOriginalExtension());

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
                    'GebDatum' => null,
                    'Vorname' => null,
                    'Nachname' => null,
                    'KatZuord' => null,
                    'NameZusatz' => null,
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
                    $countAllocation = 0;
                    $error = array();
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        set_time_limit(300);
                        // Student
                        $firstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                        $lastName = trim($Document->getValue($Document->getCell($Location['Nachname'], $RunY)));
                        $extendedName = trim($Document->getValue($Document->getCell($Location['NameZusatz'], $RunY)));
                        if ($lastName === '') {
//                            $error[] = 'Zeile: ' . ($RunY + 1) . ' Keine Zuordnung ohne Nachname.';
                        } else {

                            $GebDatum = trim($Document->getValue($Document->getCell($Location['GebDatum'], $RunY)));
                            if($GebDatum){
                                $tblPerson = Person::useService()->getPersonByNameAndBirthday($firstName, $lastName, $GebDatum);
                            } else {
                                $tblPerson = Person::useService()->getPersonByName($firstName, $lastName);
                            }

                            $tblCompany = Company::useService()->getCompanyByName($lastName, $extendedName);

                            if($tblPerson) {
                                $countAllocation++;

                                $KatZuord = trim($Document->getValue($Document->getCell($Location['KatZuord'], $RunY)));
                                $stringList = array();
                                $stringList = $this->changeGroupArrayByGroupString($stringList, $KatZuord);
                                $GroupList = array();
                                foreach($stringList as $groupString){
                                    if(isset($GroupMatchList[$groupString])){
                                        $GroupList = $this->changeGroupArrayByGroupString($GroupList, $GroupMatchList[$groupString]);
                                    }
                                }
                                if(!empty($GroupList)){
                                    $isCustody = false;
                                    if(in_array('Sorgeberechtigt', $GroupList)){
                                        $isCustody = true;
                                    }
                                    foreach($GroupList as $Group){
                                        if($isCustody && preg_match('!^Klasse*!', $Group)){
                                            // don't insert group for Custody!
                                        } elseif($isCustody && $Group !== '') {
                                            $tblGroup = Group::useService()->createGroupFromImport($Group);
                                            Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                                        } elseif(!$isCustody && preg_match('!^Klasse*!', $Group) && $Group !== ''){
                                            // division
                                            $CompleteDivision = substr($Group, 7);
                                            $division = substr($CompleteDivision, -1);
                                            $level = substr($CompleteDivision, 0, strlen($CompleteDivision)-1);
                                            if($level <= 4){
                                                $tblSchoolType = Type::useService()->getTypeById(6); // Grundschule
                                            }else {
                                                $tblSchoolType = Type::useService()->getTypeById(8); // Oberschule
                                            }

                                            $tblDivision = false;
                                            $year = 17;
                                            if ($division !== '') {
                                                $tblYear = Term::useService()->insertYear('20' . $year . '/' . ($year + 1));
                                                if ($tblYear) {
                                                    $tblPeriodList = Term::useService()->getPeriodAllByYear($tblYear);
                                                    if (!$tblPeriodList) {
                                                        // firstTerm
                                                        $tblPeriod = Term::useService()->insertPeriod(
                                                            '1. Halbjahr',
                                                            '01.08.20' . $year,
                                                            '31.01.20' . ($year + 1)
                                                        );
                                                        if ($tblPeriod) {
                                                            Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                                                        }

                                                        // secondTerm
                                                        $tblPeriod = Term::useService()->insertPeriod(
                                                            '2. Halbjahr',
                                                            '01.02.20' . ($year + 1),
                                                            '31.07.20' . ($year + 1)
                                                        );
                                                        if ($tblPeriod) {
                                                            Term::useService()->insertYearPeriod($tblYear, $tblPeriod);
                                                        }
                                                    }

                                                    if ($tblSchoolType) {
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
                                                    $error[] = 'Zeile: ' . ($RunY + 1) . ' Der Schüler konnte keiner Klasse zugeordnet werden.';
                                                }
                                            }
                                            // Klassengruppe nicht mit anlegen?!?
//                                            $tblGroup = Group::useService()->createGroupFromImport($Group);
//                                            Group::useService()->addGroupPerson($tblGroup, $tblPerson);

                                        } elseif(!$isCustody && $Group !== ''){
                                            $tblGroup = Group::useService()->createGroupFromImport($Group);
                                            Group::useService()->addGroupPerson($tblGroup, $tblPerson);
                                        }
                                    }
                                }

                            } elseif($tblCompany){
                                $countAllocation++;

                                $KatZuord = trim($Document->getValue($Document->getCell($Location['KatZuord'], $RunY)));
                                $stringList = array();
                                $stringList = $this->changeGroupArrayByGroupString($stringList, $KatZuord);
                                $GroupList = array();
                                foreach($stringList as $groupString){
                                    if(isset($GroupMatchList[$groupString])){
                                        $GroupList = $this->changeGroupArrayByGroupString($GroupList, $GroupMatchList[$groupString]);
                                    }
                                }
                                if(!empty($GroupList)){
                                    foreach($GroupList as $Group){
                                        $tblGroup = GroupCorporation::useService()->createGroupFromImport($Group);
                                        GroupCorporation::useService()->addGroupCompany($tblGroup, $tblCompany);
                                    }
                                }
                            } else {
                                $error[] = 'Zeile: ' . ($RunY + 1) . ' "'.new Bold($firstName.' '.$lastName).'" wurde nicht in Personen/Institutionen gefunden.';
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countAllocation . ' Personen/Institutionen-Gruppen zuweisungen erfolgreich angelegt.')
                        . new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                            new Panel(
                                'Fehler',
                                $error,
                                Panel::PANEL_TYPE_DANGER
                            )
                        ))));

                } else {
                    Debugger::screenDump($Location);
                    return new Warning(json_encode($Location)) . new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }
}