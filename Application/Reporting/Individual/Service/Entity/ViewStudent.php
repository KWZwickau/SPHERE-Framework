<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\School;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\ViewStudentTransfer;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_SALUTATION_SALUTATION = 'TblSalutation_Salutation';
    const TBL_PERSON_TITLE = 'TblPerson_Title';
    const TBL_PERSON_FIRST_NAME = 'TblPerson_FirstName';
    const TBL_PERSON_SECOND_NAME = 'TblPerson_SecondName';
    const TBL_PERSON_LAST_NAME = 'TblPerson_LastName';
    const TBL_PERSON_BIRTH_NAME = 'TblPerson_BirthName';
    const TBL_COMMON_GENDER_NAME = 'TblCommonGender_Name';
    const TBL_COMMON_INFORMATION_IS_ASSISTANCE = 'TblCommonInformation_IsAssistance';
    const TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY = 'TblCommonInformation_AssistanceActivity';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';
    const TBL_COMMON_BIRTHDATES_BIRTHDAY = 'TblCommonBirthDates_Birthday';
    const TBL_COMMON_BIRTHDATES_BIRTHPLACE = 'TblCommonBirthDates_Birthplace';
    const TBL_COMMON_INFORMATION_DENOMINATION = 'TblCommonInformation_Denomination';
    const TBL_COMMON_INFORMATION_NATIONALITY = 'TblCommonInformation_Nationality';
    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_CITY = 'TblCity_City';
    const TBL_CITY_DISTRICT = 'TblCity_District';
    const TBL_ADDRESS_COUNTY = 'TblAddress_County';
    const TBL_ADDRESS_STATE = 'TblState_Name';
    const TBL_ADDRESS_NATION = 'TblAddress_Nation';
    const TBL_PHONE_NUMBER = 'TblPhone_Number';
    const TBL_MAIL_ADDRESS = 'TblMail_Address';

    const BILDUNGSGANG = 'Bildungsgang';
//    const ARBEITSGEMEINSCHAFT_1 = 'Arbeitsgemeinschaft1';
//    const ARBEITSGEMEINSCHAFT_2 = 'Arbeitsgemeinschaft2';
//    const ARBEITSGEMEINSCHAFT_3 = 'Arbeitsgemeinschaft3';
    const FREMDSPRACHE_1 = 'Fremdsprache1';
    const FREMDSPRACHE_2 = 'Fremdsprache2';
    const FREMDSPRACHE_3 = 'Fremdsprache3';
    const FREMDSPRACHE_4 = 'Fremdsprache4';
//    const WAHLFACH_1 = 'Wahlfach1';
//    const WAHLFACH_2 = 'Wahlfach2';
//    const WAHLFACH_3 = 'Wahlfach3';
    const RELIGION = 'Religion';
    const PROFIL = 'Profil';
    const NEIGUNGSKURS = 'Neigungskurs';
//    const BEFREIUNGEN = 'Befreiungen';

    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE = 'TblStudentMedicalRecord_Insurance';
    const TBL_STUDENT_LOCKER_KEY_NUMBER = 'TblStudentLocker_KeyNumber';
    const TBL_STUDENT_LOCKER_LOCKER_NUMBER = 'TblStudentLocker_LockerNumber';
    const SIBLINGS_COUNT = 'Sibling_Count';

    // S1
    const TBL_SALUTATION_SALUTATION_S1 = 'TblSalutation_Salutation_S1';
    const TBL_PERSON_TITLE_S1 = 'TblPerson_Title_S1';
    const TBL_PERSON_FIRST_NAME_S1 = 'TblPerson_FirstName_S1';
    const TBL_PERSON_SECOND_NAME_S1 = 'TblPerson_SecondName_S1';
    const TBL_PERSON_LAST_NAME_S1 = 'TblPerson_LastName_S1';
    const TBL_ADDRESS_STREET_NAME_S1 = 'TblAddress_StreetName_S1';
    const TBL_ADDRESS_STREET_NUMBER_S1 = 'TblAddress_StreetNumber_S1';
    const TBL_CITY_CODE_S1 = 'TblCity_Code_S1';
    const TBL_CITY_CITY_S1 = 'TblCity_City_S1';
    const TBL_CITY_DISTRICT_S1 = 'TblCity_District_S1';
    const TBL_PHONE_NUMBER_S1 = 'TblPhone_Number_S1';
    const TBL_MAIL_ADDRESS_S1 = 'TblMail_Address_S1';

    // S2
    const TBL_SALUTATION_SALUTATION_S2 = 'TblSalutation_Salutation_S2';
    const TBL_PERSON_TITLE_S2 = 'TblPerson_Title_S2';
    const TBL_PERSON_FIRST_NAME_S2 = 'TblPerson_FirstName_S2';
    const TBL_PERSON_SECOND_NAME_S2 = 'TblPerson_SecondName_S2';
    const TBL_PERSON_LAST_NAME_S2 = 'TblPerson_LastName_S2';
    const TBL_ADDRESS_STREET_NAME_S2 = 'TblAddress_StreetName_S2';
    const TBL_ADDRESS_STREET_NUMBER_S2 = 'TblAddress_StreetNumber_S2';
    const TBL_CITY_CODE_S2 = 'TblCity_Code_S2';
    const TBL_CITY_CITY_S2 = 'TblCity_City_S2';
    const TBL_CITY_DISTRICT_S2 = 'TblCity_District_S2';
    const TBL_PHONE_NUMBER_S2 = 'TblPhone_Number_S2';
    const TBL_MAIL_ADDRESS_S2 = 'TblMail_Address_S2';

    // S3
    const TBL_SALUTATION_SALUTATION_S3 = 'TblSalutation_Salutation_S3';
    const TBL_PERSON_TITLE_S3 = 'TblPerson_Title_S3';
    const TBL_PERSON_FIRST_NAME_S3 = 'TblPerson_FirstName_S3';
    const TBL_PERSON_SECOND_NAME_S3 = 'TblPerson_SecondName_S3';
    const TBL_PERSON_LAST_NAME_S3 = 'TblPerson_LastName_S3';
    const TBL_ADDRESS_STREET_NAME_S3 = 'TblAddress_StreetName_S3';
    const TBL_ADDRESS_STREET_NUMBER_S3 = 'TblAddress_StreetNumber_S3';
    const TBL_CITY_CODE_S3 = 'TblCity_Code_S3';
    const TBL_CITY_CITY_S3 = 'TblCity_City_S3';
    const TBL_CITY_DISTRICT_S3 = 'TblCity_District_S3';
    const TBL_PHONE_NUMBER_S3 = 'TblPhone_Number_S3';
    const TBL_MAIL_ADDRESS_S3 = 'TblMail_Address_S3';

    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCommonGender_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_IsAssistance;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_AssistanceActivity;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthday;
    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Birthplace;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address;

    /**
     * @Column(type="string")
     */
    protected $Bildungsgang;
//    /**
//     * @Column(type="string")
//     */
//    protected $Arbeitsgemeinschaft1;
//    /**
//     * @Column(type="string")
//     */
//    protected $Arbeitsgemeinschaft2;
//    /**
//     * @Column(type="string")
//     */
//    protected $Arbeitsgemeinschaft3;
    /**
     * @Column(type="string")
     */
    protected $Fremdsprache1;
    /**
     * @Column(type="string")
     */
    protected $Fremdsprache2;
    /**
     * @Column(type="string")
     */
    protected $Fremdsprache3;
    /**
     * @Column(type="string")
     */
    protected $Fremdsprache4;
//    /**
//     * @Column(type="string")
//     */
//    protected $Wahlfach1;
//    /**
//     * @Column(type="string")
//     */
//    protected $Wahlfach2;
//    /**
//     * @Column(type="string")
//     */
//    protected $Wahlfach3;
    /**
     * @Column(type="string")
     */
    protected $Religion;
    /**
     * @Column(type="string")
     */
    protected $Profil;
    /**
     * @Column(type="string")
     */
    protected $Neigungskurs;
//    /**
//     * @Column(type="string")
//     */
//    protected $Befreiungen;

    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Insurance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_KeyNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Identifier;
    /**
     * @Column(type="string")
     */
    protected $Sibling_Count;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S1;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City_S1;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S1;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S1;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S1;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S2;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City_S2;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S2;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S2;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S2;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_S3;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_City_S3;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_S3;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_S3;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_S3;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION, 'Schüler: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE, 'Schüler: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME, 'Schüler: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME, 'Schüler: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME, 'Schüler: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME, 'Schüler: Geburtsname');
        $this->setNameDefinition(self::TBL_COMMON_GENDER_NAME, 'Schüler: Geschlecht');
        $this->setNameDefinition(self::TBL_COMMON_REMARK, 'Schüler: Bemerkung Person');
        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHDAY, 'Schüler: Geburtstag');
        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHPLACE, 'Schüler: Geburtsort');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_DENOMINATION, 'Schüler: Konfession');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_NATIONALITY, 'Schüler: Staatsangehörigkeit');

        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'Schüler: Mitarbeitsbereitschaft');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'Schüler: Mitarbeit Tätigkeit');

        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME, 'Schüler: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER, 'Schüler: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE, 'Schüler: PLZ');
        $this->setNameDefinition(self::TBL_CITY_CITY, 'Schüler: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT, 'Schüler: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY, 'Schüler: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE, 'Schüler: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION, 'Schüler: Land');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER, 'Schüler: Telefonnummer');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS, 'Schüler: E-Mail');

        $this->setNameDefinition(self::BILDUNGSGANG, 'Schülerakte: Bildungsgang');
//        $this->setNameDefinition(self::ARBEITSGEMEINSCHAFT_1, 'Arbeitsgemeinschaft 1');
//        $this->setNameDefinition(self::ARBEITSGEMEINSCHAFT_2, 'Arbeitsgemeinschaft 2');
//        $this->setNameDefinition(self::ARBEITSGEMEINSCHAFT_3, 'Arbeitsgemeinschaft 3');
        $this->setNameDefinition(self::FREMDSPRACHE_1, 'Fremdsprache 1');
        $this->setNameDefinition(self::FREMDSPRACHE_2, 'Fremdsprache 2');
        $this->setNameDefinition(self::FREMDSPRACHE_3, 'Fremdsprache 3');
        $this->setNameDefinition(self::FREMDSPRACHE_4, 'Fremdsprache 4');
//        $this->setNameDefinition(self::WAHLFACH_1, 'Wahlfach 1');
//        $this->setNameDefinition(self::WAHLFACH_2, 'Wahlfach 2');
//        $this->setNameDefinition(self::WAHLFACH_3, 'Wahlfach 3');
        $this->setNameDefinition(self::RELIGION, 'Religion');
        $this->setNameDefinition(self::PROFIL, 'Profil');
        $this->setNameDefinition(self::NEIGUNGSKURS, 'Neigungskurs');
//        $this->setNameDefinition(self::BEFREIUNGEN, 'Befreiungen');

        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE, 'Versicherung');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_KEY_NUMBER, 'Schließfach Schlüsselnummer');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_NUMBER, 'Schließfachnummer');
        $this->setNameDefinition(self::TBL_STUDENT_IDENTIFIER, 'Schülerakte: Schülernummer');
        $this->setNameDefinition(self::SIBLINGS_COUNT, 'Anzahl Geschwister');
        // S1
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S1, 'S1 Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S1, 'S1 Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S1, 'S1 Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S1, 'S1 Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S1, 'S1 Nachname');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S1, 'S1 Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S1, 'S1 Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_S1, 'S1 PLZ');
        $this->setNameDefinition(self::TBL_CITY_CITY_S1, 'S1 Stadt');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S1, 'S1 Ortsteil');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S1, 'S1 Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S1, 'S1 E-Mail');
        // S2
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S2, 'S2 Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S2, 'S2 Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S2, 'S2 Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S2, 'S2 Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S2, 'S2 Nachname');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S2, 'S2 Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S2, 'S2 Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_S2, 'S2 PLZ');
        $this->setNameDefinition(self::TBL_CITY_CITY_S2, 'S2 Stadt');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S2, 'S2 Ortsteil');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S2, 'S2 Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S2, 'S2 E-Mail');
        // S3
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_S3, 'S3 Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_S3, 'S3 Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_S3, 'S3 Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_S3, 'S3 Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_S3, 'S3 Nachname');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_S3, 'S3 Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_S3, 'S3 Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_S3, 'S3 PLZ');
        $this->setNameDefinition(self::TBL_CITY_CITY_S3, 'S3 Stadt');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_S3, 'S3 Ortsteil');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_S3, 'S3 Telefon');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_S3, 'S3 E-Mail');

        //GroupDefinition
        $this->setGroupDefinition('Grunddaten', array(
            self::TBL_SALUTATION_SALUTATION,
            self::TBL_PERSON_TITLE,
            self::TBL_PERSON_FIRST_NAME,
            self::TBL_PERSON_SECOND_NAME,
            self::TBL_PERSON_LAST_NAME,
//            self::TBL_PERSON_BIRTH_NAME,
        ));
        $this->setGroupDefinition('Personendaten', array(
            self::TBL_COMMON_BIRTHDATES_BIRTHDAY,
            self::TBL_COMMON_BIRTHDATES_BIRTHPLACE,
            self::TBL_COMMON_GENDER_NAME,
            self::TBL_COMMON_INFORMATION_NATIONALITY,
            self::TBL_COMMON_INFORMATION_DENOMINATION,
            self::TBL_COMMON_INFORMATION_IS_ASSISTANCE,
            self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY,
            self::TBL_COMMON_REMARK,
        ));

        $this->setGroupDefinition('Adressdaten', array(
            self::TBL_ADDRESS_STREET_NAME,
            self::TBL_ADDRESS_STREET_NUMBER,
            self::TBL_CITY_CODE,
            self::TBL_CITY_CITY,
            self::TBL_CITY_DISTRICT,
            self::TBL_ADDRESS_COUNTY,
            self::TBL_ADDRESS_STATE,
            self::TBL_ADDRESS_NATION,
        ));
        $this->setGroupDefinition('Kontaktdaten', array(
            self::TBL_PHONE_NUMBER,
            self::TBL_MAIL_ADDRESS,
        ));

        $this->setGroupDefinition('Schülerakte', array(
            self::BILDUNGSGANG,
            self::TBL_STUDENT_IDENTIFIER,
//            self::ARBEITSGEMEINSCHAFT_1,
//            self::ARBEITSGEMEINSCHAFT_2,
//            self::ARBEITSGEMEINSCHAFT_3,
            self::FREMDSPRACHE_1,
            self::FREMDSPRACHE_2,
            self::FREMDSPRACHE_3,
            self::FREMDSPRACHE_4,
//            self::WAHLFACH_1,
//            self::WAHLFACH_2,
//            self::WAHLFACH_3,
            self::RELIGION,
            self::PROFIL,
            self::NEIGUNGSKURS,
//            self::BEFREIUNGEN,
//            self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE,
//            self::TBL_STUDENT_LOCKER_KEY_NUMBER,
//            self::TBL_STUDENT_LOCKER_LOCKER_NUMBER,
//            self::SIBLINGS_COUNT
        ));

        $this->setGroupDefinition('Sorge. S1 (Zusatzinfo)', array(
            self::TBL_SALUTATION_SALUTATION_S1,
            self::TBL_PERSON_TITLE_S1,
            self::TBL_PERSON_FIRST_NAME_S1,
            self::TBL_PERSON_SECOND_NAME_S1,
            self::TBL_PERSON_LAST_NAME_S1,
            self::TBL_ADDRESS_STREET_NAME_S1,
            self::TBL_ADDRESS_STREET_NUMBER_S1,
            self::TBL_CITY_CODE_S1,
            self::TBL_CITY_CITY_S1,
            self::TBL_CITY_DISTRICT_S1,
            self::TBL_PHONE_NUMBER_S1,
            self::TBL_MAIL_ADDRESS_S1
        ));
        $this->setGroupDefinition('Sorge. S2 (Zusatzinfo)', array(
            self::TBL_SALUTATION_SALUTATION_S2,
            self::TBL_PERSON_TITLE_S2,
            self::TBL_PERSON_FIRST_NAME_S2,
            self::TBL_PERSON_SECOND_NAME_S2,
            self::TBL_PERSON_LAST_NAME_S2,
            self::TBL_ADDRESS_STREET_NAME_S2,
            self::TBL_ADDRESS_STREET_NUMBER_S2,
            self::TBL_CITY_CODE_S2,
            self::TBL_CITY_CITY_S2,
            self::TBL_CITY_DISTRICT_S2,
            self::TBL_PHONE_NUMBER_S2,
            self::TBL_MAIL_ADDRESS_S2
        ));
        $this->setGroupDefinition('Sorge. S3 (Zusatzinfo)', array(
            self::TBL_SALUTATION_SALUTATION_S3,
            self::TBL_PERSON_TITLE_S3,
            self::TBL_PERSON_FIRST_NAME_S3,
            self::TBL_PERSON_SECOND_NAME_S3,
            self::TBL_PERSON_LAST_NAME_S3,
            self::TBL_ADDRESS_STREET_NAME_S3,
            self::TBL_ADDRESS_STREET_NUMBER_S3,
            self::TBL_CITY_CODE_S3,
            self::TBL_CITY_CITY_S3,
            self::TBL_CITY_DISTRICT_S3,
            self::TBL_PHONE_NUMBER_S3,
            self::TBL_MAIL_ADDRESS_S3,
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S1);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_S1);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_S1);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_S1);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_S1);
        $this->setDisableDefinition(self::TBL_CITY_CODE_S1);
        $this->setDisableDefinition(self::TBL_CITY_CITY_S1);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_S1);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_S1);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_S1);
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S2);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_S2);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_S2);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_S2);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_S2);
        $this->setDisableDefinition(self::TBL_CITY_CODE_S2);
        $this->setDisableDefinition(self::TBL_CITY_CITY_S2);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_S2);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_S2);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_S2);
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S3);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_S3);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_S3);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_S3);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_S3);
        $this->setDisableDefinition(self::TBL_CITY_CODE_S3);
        $this->setDisableDefinition(self::TBL_CITY_CITY_S3);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_S3);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_S3);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_S3);
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return void|AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }

    /**
     * Define Property Field-Type and additional Data
     *
     * @param string $PropertyName __CLASS__::{CONSTANT_}
     * @param null|string $Placeholder
     * @param null|string $Label
     * @param IIconInterface|null $Icon
     * @param bool $doResetCount Reset ALL FieldName calculations e.g. FieldName[23] -> FieldName[1]
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
            case self::SIBLINGS_COUNT:
                $PropertyCount = $this->calculateFormFieldCount( $PropertyName, $doResetCount );
                $Field = new NumberField( $PropertyName.'['.$PropertyCount.']',
                    $Placeholder, $Label, $Icon
                );
                break;
            case self::TBL_CITY_CITY:
                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_COMMON_BIRTHDATES_BIRTHPLACE:
                $Data = Common::useService()->getPropertyList( new TblCommonBirthDates(), TblCommonBirthDates::ATTR_BIRTHPLACE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_COMMON_GENDER_NAME:
                $Data = Common::useService()->getPropertyList( new TblCommonGender(), TblCommonGender::ATTR_NAME );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_SALUTATION_SALUTATION:
                $Data = Person::useService()->getPropertyList( new TblSalutation(''), TblSalutation::ATTR_SALUTATION );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            case self::BILDUNGSGANG:
                $Data = Course::useService()->getPropertyList( new TblCourse(), TblCourse::ATTR_NAME );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
