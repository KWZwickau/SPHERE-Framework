<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewPerson extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_SALUTATION_SALUTATION = 'TblSalutation_Salutation';
    const TBL_PERSON_TITLE = 'TblPerson_Title';
    const TBL_PERSON_FIRST_NAME = 'TblPerson_FirstName';
    const TBL_PERSON_SECOND_NAME = 'TblPerson_SecondName';
    const TBL_PERSON_LAST_NAME = 'TblPerson_LastName';
    const TBL_PERSON_BIRTH_NAME = 'TblPerson_BirthName';

    const TBL_COMMON_BIRTHDATES_BIRTHDAY = 'TblCommonBirthDates_Birthday';
    const TBL_COMMON_BIRTHDATES_BIRTHPLACE = 'TblCommonBirthDates_Birthplace';
    const TBL_COMMON_GENDER_NAME = 'TblCommonGender_Name';
    const TBL_COMMON_INFORMATION_NATIONALITY = 'TblCommonInformation_Nationality';
    const TBL_COMMON_INFORMATION_DENOMINATION = 'TblCommonInformation_Denomination';
    const TBL_COMMON_INFORMATION_IS_ASSISTANCE = 'TblCommonInformation_IsAssistance';
    const TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY = 'TblCommonInformation_AssistanceActivity';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';

    /**
     * @return array
     */
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
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
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
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION, 'Person: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE, 'Person: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME, 'Person: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME, 'Person: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME, 'Person: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME, 'Person: Geburtsname');

        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHDAY, 'Person: Geburtstag');
        $this->setNameDefinition(self::TBL_COMMON_BIRTHDATES_BIRTHPLACE, 'Person: Geburtsort');
        $this->setNameDefinition(self::TBL_COMMON_GENDER_NAME, 'Person: Geschlecht');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_NATIONALITY, 'Person: Staatsangehörigkeit');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_DENOMINATION, 'Person: Konfession');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'Person: Mitarbeitsbereitschaft');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'Person: Mitarbeit Tätigkeit');
        $this->setNameDefinition(self::TBL_COMMON_REMARK, 'Person: Bemerkung zur Person');

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

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
//        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S1);
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
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
