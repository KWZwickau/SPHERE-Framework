<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Transfer\Import\Chemnitz\Service\Address;
use SPHERE\Application\Transfer\Import\Chemnitz\Service\Common;
use SPHERE\Application\Transfer\Import\Chemnitz\Service\Custody;
use SPHERE\Application\Transfer\Import\Chemnitz\Service\Person;
use SPHERE\Application\Transfer\Import\Chemnitz\Service\Phone;
use SPHERE\Application\Transfer\Import\Chemnitz\Service\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Repository\Debugger;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Service
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz
 */
class Service
{

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @param null $DivisionId
     * @return IFormInterface|Danger|Redirect|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createStudentsFromFile(
        IFormInterface $Form = null,
        UploadedFile $File = null,
        $DivisionId = null
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $File || null === $DivisionId) {
            return $Form;
        }


        $tblDivision = Division::useService()->getDivisionById($DivisionId);

        if (null !== $File) {
            if ($File->getError()) {
                $Form->setError('File', 'Fehler');
            } else {

                if ($tblDivision) {

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
                        'Vorname V.' => null,
                        'Vorname M.' => null,
                        'Name' => null,
                        'Konfession' => null,
                        'Straße' => null,
                        'Hausnr.' => null,
                        'PLZ Ort' => null,
                        'Schüler' => null,
                        'Geburtsdatum' => null,
                        'Geburtsort' => null,
                        'Import Vater' => null,
                        'Import Mutter' => null,
                    );
                    for ($RunX = 0; $RunX < $X; $RunX++) {
                        $Value = $Document->getValue($Document->getCell($RunX, 0));
                        if (array_key_exists($Value, $Location)) {
                            $Location[$Value] = $RunX;
                        }
                    }

                    /**
                     * Import
                     */
                    if (!in_array(null, $Location, true)) {
                        $countStudent = 0;
                        $countFather = 0;
                        $countMother = 0;
                        $countFatherExists = 0;
                        $countMotherExists = 0;

                        for ($RunY = 1; $RunY < $Y; $RunY++) {
                            // Student
                            $tblPerson = $this->usePeoplePerson()->createPersonFromImport(
                                \SPHERE\Application\People\Person\Person::useService()->getSalutationById(3),
                                //Schüler
                                '',
                                trim($Document->getValue($Document->getCell($Location['Schüler'], $RunY))),
                                '',
                                trim($Document->getValue($Document->getCell($Location['Name'], $RunY))),
                                array(
                                    0 => Group::useService()->getGroupById(1),    //Personendaten
                                    1 => Group::useService()->getGroupById(3)            //Schüler
                                )
                            );

                            if ($tblPerson !== false) {
                                $countStudent++;

                                $LastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                                $City = trim($Document->getValue($Document->getCell($Location['PLZ Ort'], $RunY)));
                                $CityCode = substr($City, 0, 5);
                                $CityName = substr($City, 6);

                                $this->usePeopleMetaCommon()->createMetaFromImport(
                                    $tblPerson,
                                    date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP(
                                        trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                            $RunY))))),
                                    trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                    trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY)))
                                );

                                // add student to division
                                Division::useService()->insertDivisionStudent($tblDivision, $tblPerson);

                                // Father
                                $tblPersonFather = null;
                                $FatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname V.'],
                                    $RunY)));
                                if ($FatherFirstName !== ''
                                    && trim($Document->getValue($Document->getCell($Location['Import Vater'],
                                        $RunY))) !== 'nein'
                                ) {
                                    $tblPersonFatherExists = $this->usePeoplePerson()->getPersonExists(
                                        $FatherFirstName,
                                        $LastName,
                                        $CityCode
                                    );
                                    if (!$tblPersonFatherExists) {
                                        $tblPersonFather = $this->usePeoplePerson()->createPersonFromImport(
                                            \SPHERE\Application\People\Person\Person::useService()->getSalutationById(1),
                                            '',
                                            $FatherFirstName,
                                            '',
                                            $LastName,
                                            array(
                                                0 => Group::useService()->getGroupById(1),        //Personendaten
                                                1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                                            )
                                        );

                                        $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                            $tblPersonFather,
                                            $tblPerson,
                                            \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                        );

                                        $countFather++;
                                    } else {

                                        $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                            $tblPersonFatherExists,
                                            $tblPerson,
                                            \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                        );

                                        $countFatherExists++;
                                    }
                                }

                                // Mother
                                $tblPersonMother = null;
                                $MotherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname M.'],
                                    $RunY)));
                                if ($MotherFirstName !== ''
                                    && trim($Document->getValue($Document->getCell($Location['Import Mutter'],
                                        $RunY))) !== 'nein'
                                ) {

                                    $tblPersonMotherExists = $this->usePeoplePerson()->getPersonExists(
                                        $MotherFirstName,
                                        $LastName,
                                        $CityCode
                                    );
                                    if (!$tblPersonMotherExists) {
                                        $tblPersonMother = $this->usePeoplePerson()->createPersonFromImport(
                                            \SPHERE\Application\People\Person\Person::useService()->getSalutationById(2),
                                            '',
                                            $MotherFirstName,
                                            '',
                                            $LastName,
                                            array(
                                                0 => Group::useService()->getGroupById(1),        //Personendaten
                                                1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                                            )
                                        );

                                        $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                            $tblPersonMother,
                                            $tblPerson,
                                            \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                        );

                                        $countMother++;
                                    } else {
                                        $countMotherExists++;

                                        $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                            $tblPersonMotherExists,
                                            $tblPerson,
                                            \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                        );
                                    }
                                }

                                // Addresses
                                $StreetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                                $StreetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                    $RunY)));
                                $this->useContactAddress()->createAddressToPersonFromImport(
                                    $tblPerson,
                                    $StreetName,
                                    $StreetNumber,
                                    $CityCode,
                                    $CityName,
                                    ''
                                );
                                if ($tblPersonFather !== null) {
                                    $this->useContactAddress()->createAddressToPersonFromImport(
                                        $tblPersonFather,
                                        $StreetName,
                                        $StreetNumber,
                                        $CityCode,
                                        $CityName,
                                        ''
                                    );
                                }
                                if ($tblPersonMother !== null) {
                                    $this->useContactAddress()->createAddressToPersonFromImport(
                                        $tblPersonMother,
                                        $StreetName,
                                        $StreetNumber,
                                        $CityCode,
                                        $CityName,
                                        ''
                                    );
                                }
                            }
                        }

                        return
                            new Success('Es wurden ' . $countStudent . ' Schüler erfolgreich angelegt.') .
                            new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                            ($countFatherExists > 0 ?
                                new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                            new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                            ($countMotherExists > 0 ?
                                new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '');
                    } else {
                        Debugger::screenDump($Location);
                        return new Danger(
                            "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                    }
                }
            }
        }
        return new Danger('File nicht gefunden');
    }

    /**
     * @return Person
     */
    public static function usePeoplePerson()
    {

        return new Person(
            new Identifier('People', 'Person', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../People/Person/Service/Entity', 'SPHERE\Application\People\Person\Service\Entity'
        );
    }

    /**
     * @return Common
     */
    public static function usePeopleMetaCommon()
    {

        return new Common(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../People/Meta/Common/Service/Entity',
            'SPHERE\Application\People\Meta\Common\Service\Entity'
        );
    }

    /**
     * @return Relationship
     */
    public static function usePeopleRelationship()
    {

        return new Relationship(
            new Identifier('People', 'Relationship', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../People/Relationship/Service/Entity',
            'SPHERE\Application\People\Relationship\Service\Entity'
        );
    }

    /**
     * @return Address
     */
    public static function useContactAddress()
    {

        return new Address(
            new Identifier('Contact', 'Address', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../Contact/Address/Service/Entity', 'SPHERE\Application\Contact\Address\Service\Entity'
        );
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile $File
     *
     * @return IFormInterface|Danger|string
     *
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createPersonsFromFile(IFormInterface $Form = null, UploadedFile $File = null)
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
                $File = $File->move($File->getPath(), $File->getFilename() . '.' . $File->getClientOriginalExtension());
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
                    'Anrede' => null,
                    'Firma' => null,
                    'Name' => null,
                    'Vorname' => null,
                    'Strasse' => null,
                    'Ort' => null,
                    'Plz' => null,
                    'Telefon_private' => null,
                    'Telefon_dienst' => null,
                    'Fax' => null,
                    'Mail' => null,
                    'Beruf' => null,
                    'Freunde' => null,
                    'Post' => null,
                    'Gebet' => null,
                    'Partner' => null,
                    'Verein' => null,
                    'Offizielle' => null,
                    'Ehemalige' => null,
                    'Sonstiges' => null,
                    'Sonstiges2' => null,

                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = $Document->getValue($Document->getCell($RunX, 0));
                    if (array_key_exists($Value, $Location)) {
                        $Location[$Value] = $RunX;
                    }
                }

                /**
                 * Import
                 */
                if (!in_array(null, $Location, true)) {
                    $countNewPerson = 0;
                    $countUpdatePerson = 0;
                    $countPhone = 0;
                    $countOutSortedPersons = 0;

                    // create groups
                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        if (trim($Document->getValue($Document->getCell($Location['Freunde'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Freunde');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Post'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Post');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Gebet'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Gebet');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Partner'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Partner');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Verein'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Verein');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Offizielle'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Offizielle');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Ehemalige'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Ehemalige');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Sonstiges'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Sonstiges');
                        }
                        if (trim($Document->getValue($Document->getCell($Location['Sonstiges2'], $RunY))) == 'Wahr') {
                            Group::useService()->createGroupFromImport('Sonstiges2');
                        }
                    }

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        if (trim($Document->getValue($Document->getCell($Location['Firma'], $RunY))) == 'Falsch') {

                            $FirstName = trim($Document->getValue($Document->getCell($Location['Vorname'], $RunY)));
                            $LastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                            $ZipCode = trim($Document->getValue($Document->getCell($Location['Plz'], $RunY)));
                            if (strpos($FirstName, 'u.') === false && strpos($FirstName, ',') === false) {

                                $tblPerson = $this->usePeoplePerson()->getPersonExists($FirstName, $LastName, $ZipCode);

                                if (!$tblPerson) {

                                    $tblPerson = $this->usePeoplePerson()->createPersonFromImport(
                                        null,
                                        '',
                                        $FirstName,
                                        '',
                                        $LastName
                                    );
                                    Group::useService()->addGroupPerson(Group::useService()->getGroupById(1),
                                        $tblPerson); //Personendaten
                                    $countNewPerson++;

                                    //Address
                                    $Street = trim($Document->getValue($Document->getCell($Location['Strasse'],
                                        $RunY)));
                                    if (preg_match_all('!\d+!', $Street, $matches)) {
                                        $pos = strpos($Street, $matches[0][0]);
                                        if ($pos !== null) {
                                            $StreetName = trim(substr($Street, 0, $pos));
                                            $StreetNumber = trim(substr($Street, $pos));
                                            $this->useContactAddress()->createAddressToPersonFromImport(
                                                $tblPerson,
                                                $StreetName,
                                                $StreetNumber,
                                                trim($Document->getValue($Document->getCell($Location['Plz'], $RunY))),
                                                trim($Document->getValue($Document->getCell($Location['Ort'], $RunY))),
                                                ''
                                            );
                                        }
                                    }
                                } else {
                                    $countUpdatePerson++;
                                }

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Telefon_private'],
                                        $RunY)))) !== ''
                                ) {
                                    $tblType = \SPHERE\Application\Contact\Phone\Phone::useService()->getTypeById(1);
                                    if (0 === strpos($Number, '01')) {
                                        $tblType = \SPHERE\Application\Contact\Phone\Phone::useService()->getTypeById(2);
                                    }
                                    $this->useContactPhone()->createPhoneToPersonFromImport(
                                        $tblPerson,
                                        $Number,
                                        $tblType
                                    );
                                    $countPhone++;
                                }

                                if (($Number = trim($Document->getValue($Document->getCell($Location['Telefon_dienst'],
                                        $RunY)))) !== ''
                                ) {
                                    $tblType = \SPHERE\Application\Contact\Phone\Phone::useService()->getTypeById(3);
                                    if (0 === strpos($Number, '01')) {
                                        $tblType = \SPHERE\Application\Contact\Phone\Phone::useService()->getTypeById(4);
                                    }
                                    $this->useContactPhone()->createPhoneToPersonFromImport(
                                        $tblPerson,
                                        $Number,
                                        $tblType
                                    );
                                    $countPhone++;
                                }

                                // add group
                                if (trim($Document->getValue($Document->getCell($Location['Freunde'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Freunde'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Post'], $RunY))) == 'Wahr') {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Post'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Gebet'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Gebet'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Partner'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Partner'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Verein'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Verein'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Offizielle'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Offizielle'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Ehemalige'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Ehemalige'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Sonstiges'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Sonstiges'),
                                        $tblPerson
                                    );
                                }
                                if (trim($Document->getValue($Document->getCell($Location['Sonstiges2'],
                                        $RunY))) == 'Wahr'
                                ) {
                                    Group::useService()->addGroupPerson(
                                        Group::useService()->getGroupByName('Sonstiges2'),
                                        $tblPerson
                                    );
                                }

                                $Occupation = trim($Document->getValue($Document->getCell($Location['Beruf'], $RunY)));
                                if ($Occupation !== 'k.A.') {
                                    $this->usePeopleMetaCustody()->createMetaFromImport(
                                        $tblPerson,
                                        $Occupation
                                    );
                                }

                            } else {
                                $countOutSortedPersons++;
                            }
                        }
                    }

                    return
                        new Warning('Es wurden ' . $countOutSortedPersons . ' Personen aussortiert (Vorname enthält "u." oder ",").') .
                        new Success('Es wurden ' . $countNewPerson . ' neue Personen erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countUpdatePerson . ' Personen erfolgreich geupdated.') .
                        new Success('Es wurden ' . $countPhone . ' Telefonnummern erfolgreich angelegt.');
                } else {
                    Debugger::screenDump($Location);
                    return new Danger("File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }

        return new Danger('File nicht gefunden');
    }

    /**
     * @return Phone
     */
    public static function useContactPhone()
    {

        return new Phone(
            new Identifier('Contact', 'Phone', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../Contact/Phone/Service/Entity', 'SPHERE\Application\Contact\Phone\Service\Entity'
        );
    }

    /**
     * @return Custody
     */
    public static function usePeopleMetaCustody()
    {

        return new Custody(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/../../../People/Meta/Custody/Service/Entity',
            'SPHERE\Application\People\Meta\Custody\Service\Entity'
        );
    }

    /**
     * @param IFormInterface|null $Stage
     * @param null $Select
     *
     * @return IFormInterface
     */
    public function getClass(IFormInterface $Stage = null, $Select = null)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Select) {
            return $Stage;
        }

        $tblDivision = Division::useService()->getDivisionById($Select['Division']);

        return new Redirect('/Transfer/Import/Chemnitz/Student/Import', 0, array(
            'DivisionId' => $tblDivision->getId(),
        ));
    }

    /**
     * @param IFormInterface|null $Form
     * @param UploadedFile|null $File
     * @return IFormInterface|Danger|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     */
    public function createInterestedPersonsFromFile(
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
                    'Vorname V.' => null,
                    'Vorname M.' => null,
                    'Name' => null,
                    'Konfession' => null,
                    'Straße' => null,
                    'Hausnr.' => null,
                    'PLZ Ort' => null,
                    'Schüler' => null,
                    'Geburtsdatum' => null,
                    'Geburtsort' => null,
                    'Import Vater' => null,
                    'Import Mutter' => null,
                    'Anm.Datum' => null,
                    'Klasse' => null,
                    'Schuljahr' => null,
                    'Schulart 1' => null,
                    'Schulart 2' => null,
                );
                for ($RunX = 0; $RunX < $X; $RunX++) {
                    $Value = $Document->getValue($Document->getCell($RunX, 0));
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

                    for ($RunY = 1; $RunY < $Y; $RunY++) {
                        // InterestedPerson
                        $tblPerson = $this->usePeoplePerson()->createPersonFromImport(
                            \SPHERE\Application\People\Person\Person::useService()->getSalutationById(3),
                            //Schüler
                            '',
                            trim($Document->getValue($Document->getCell($Location['Schüler'], $RunY))),
                            '',
                            trim($Document->getValue($Document->getCell($Location['Name'], $RunY))),
                            array(
                                0 => Group::useService()->getGroupById(1),    // Personendaten
                                1 => Group::useService()->getGroupById(2)     // Intessentendaten
                            )
                        );

                        if ($tblPerson !== false) {
                            $countInterestedPerson++;

                            $LastName = trim($Document->getValue($Document->getCell($Location['Name'], $RunY)));
                            $City = trim($Document->getValue($Document->getCell($Location['PLZ Ort'], $RunY)));
                            $CityCode = substr($City, 0, 5);
                            $CityName = substr($City, 6);

                            $this->usePeopleMetaCommon()->createMetaFromImport(
                                $tblPerson,
                                date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP(
                                    trim($Document->getValue($Document->getCell($Location['Geburtsdatum'],
                                        $RunY))))),
                                trim($Document->getValue($Document->getCell($Location['Geburtsort'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Konfession'], $RunY)))
                            );

                            $tblOptionTypeA = null;
                            if (($OptionTypeA = trim($Document->getValue($Document->getCell($Location['Schulart 1'],
                                    $RunY)))) !== ''
                            ) {
                                if ($OptionTypeA == 'Oberschule') {
                                    $tblOptionTypeA = Type::useService()->getTypeById(8);
                                } elseif ($OptionTypeA == 'Gymnasium') {
                                    $tblOptionTypeA = Type::useService()->getTypeById(7);
                                } elseif ($OptionTypeA == 'Grundschule') {
                                    $tblOptionTypeA = Type::useService()->getTypeById(6);
                                }
                            }
                            $tblOptionTypeB = null;
                            if (($OptionTypeB = trim($Document->getValue($Document->getCell($Location['Schulart 2'],
                                    $RunY)))) !== ''
                            ) {
                                if ($OptionTypeB == 'Oberschule') {
                                    $tblOptionTypeB = Type::useService()->getTypeById(8);
                                } elseif ($OptionTypeB == 'Gymnasium') {
                                    $tblOptionTypeB = Type::useService()->getTypeById(7);
                                } elseif ($OptionTypeB == 'Grundschule') {
                                    $tblOptionTypeB = Type::useService()->getTypeById(6);
                                }
                            }
                            Prospect::useService()->insertMeta(
                                $tblPerson,
                                date('Y-m-d', \PHPExcel_Shared_Date::ExcelToPHP(
                                    trim($Document->getValue($Document->getCell($Location['Anm.Datum'],
                                        $RunY))))),
                                '',
                                '',
                                trim($Document->getValue($Document->getCell($Location['Schuljahr'], $RunY))),
                                trim($Document->getValue($Document->getCell($Location['Klasse'], $RunY))),
                                $tblOptionTypeA,
                                $tblOptionTypeB,
                                ''
                            );

                            // Father
                            $tblPersonFather = null;
                            $FatherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname V.'],
                                $RunY)));
                            if ($FatherFirstName !== ''
                                && trim($Document->getValue($Document->getCell($Location['Import Vater'],
                                    $RunY))) !== 'nein'
                            ) {
                                $tblPersonFatherExists = $this->usePeoplePerson()->getPersonExists(
                                    $FatherFirstName,
                                    $LastName,
                                    $CityCode
                                );
                                if (!$tblPersonFatherExists) {
                                    $tblPersonFather = $this->usePeoplePerson()->createPersonFromImport(
                                        \SPHERE\Application\People\Person\Person::useService()->getSalutationById(1),
                                        '',
                                        $FatherFirstName,
                                        '',
                                        $LastName,
                                        array(
                                            0 => Group::useService()->getGroupById(1),        //Personendaten
                                            1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                                        )
                                    );

                                    $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                        $tblPersonFather,
                                        $tblPerson,
                                        \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                    );

                                    $countFather++;
                                } else {

                                    $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                        $tblPersonFatherExists,
                                        $tblPerson,
                                        \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                    );

                                    $countFatherExists++;
                                }
                            }

                            // Mother
                            $tblPersonMother = null;
                            $MotherFirstName = trim($Document->getValue($Document->getCell($Location['Vorname M.'],
                                $RunY)));
                            if ($MotherFirstName !== ''
                                && trim($Document->getValue($Document->getCell($Location['Import Mutter'],
                                    $RunY))) !== 'nein'
                            ) {

                                $tblPersonMotherExists = $this->usePeoplePerson()->getPersonExists(
                                    $MotherFirstName,
                                    $LastName,
                                    $CityCode
                                );
                                if (!$tblPersonMotherExists) {
                                    $tblPersonMother = $this->usePeoplePerson()->createPersonFromImport(
                                        \SPHERE\Application\People\Person\Person::useService()->getSalutationById(2),
                                        '',
                                        $MotherFirstName,
                                        '',
                                        $LastName,
                                        array(
                                            0 => Group::useService()->getGroupById(1),        //Personendaten
                                            1 => Group::useService()->getGroupById(4)           //Sorgeberechtigt
                                        )
                                    );

                                    $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                        $tblPersonMother,
                                        $tblPerson,
                                        \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                    );

                                    $countMother++;
                                } else {
                                    $countMotherExists++;

                                    $this->usePeopleRelationship()->createRelationshipToPersonFromImport(
                                        $tblPersonMotherExists,
                                        $tblPerson,
                                        \SPHERE\Application\People\Relationship\Relationship::useService()->getTypeById(1) //Sorgeberechtigt
                                    );
                                }
                            }

                            // Addresses
                            $StreetName = trim($Document->getValue($Document->getCell($Location['Straße'], $RunY)));
                            $StreetNumber = trim($Document->getValue($Document->getCell($Location['Hausnr.'],
                                $RunY)));
                            $this->useContactAddress()->createAddressToPersonFromImport(
                                $tblPerson,
                                $StreetName,
                                $StreetNumber,
                                $CityCode,
                                $CityName,
                                ''
                            );
                            if ($tblPersonFather !== null) {
                                $this->useContactAddress()->createAddressToPersonFromImport(
                                    $tblPersonFather,
                                    $StreetName,
                                    $StreetNumber,
                                    $CityCode,
                                    $CityName,
                                    ''
                                );
                            }
                            if ($tblPersonMother !== null) {
                                $this->useContactAddress()->createAddressToPersonFromImport(
                                    $tblPersonMother,
                                    $StreetName,
                                    $StreetNumber,
                                    $CityCode,
                                    $CityName,
                                    ''
                                );
                            }
                        }
                    }

                    return
                        new Success('Es wurden ' . $countInterestedPerson . ' Intessenten erfolgreich angelegt.') .
                        new Success('Es wurden ' . $countFather . ' Väter erfolgreich angelegt.') .
                        ($countFatherExists > 0 ?
                            new Warning($countFatherExists . ' Väter exisistieren bereits.') : '') .
                        new Success('Es wurden ' . $countMother . ' Mütter erfolgreich angelegt.') .
                        ($countMotherExists > 0 ?
                            new Warning($countMotherExists . ' Mütter exisistieren bereits.') : '');
                } else {
                    Debugger::screenDump($Location);
                    return new Danger(
                        "File konnte nicht importiert werden, da nicht alle erforderlichen Spalten gefunden wurden");
                }
            }
        }
        return new Danger('File nicht gefunden');
    }
}
