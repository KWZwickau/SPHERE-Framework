<?php
namespace SPHERE\Application\People\Meta\Common\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaCommon")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaCommon extends AbstractView
{

    const TBL_COMMON_ID = 'TblCommon_Id';
    const TBL_COMMON_SERVICE_TBL_PERSON = 'TblCommon_serviceTblPerson';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';
    const TBL_COMMON_TBL_COMMON_BIRTH_DATES = 'TblCommon_tblCommonBirthDates';
    const TBL_COMMON_TBL_COMMON_INFORMATION = 'TblCommon_tblCommonInformation';

    const TBL_COMMON_BIRTH_DATES_ID = 'TblCommonBirthDates_Id';
    const TBL_COMMON_BIRTH_DATES_BIRTHDAY = 'TblCommonBirthDates_Birthday';
    const TBL_COMMON_BIRTH_DATES_BIRTHPLACE = 'TblCommonBirthDates_Birthplace';
    const TBL_COMMON_BIRTH_DATES_GENDER = 'TblCommonBirthDates_Gender';

    const TBL_COMMON_INFORMATION_NATIONALITY = 'TblCommonInformation_Nationality';
    const TBL_COMMON_INFORMATION_DENOMINATION = 'TblCommonInformation_Denomination';
    const TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY = 'TblCommonInformation_AssistanceActivity';
    const TBL_COMMON_INFORMATION_IS_ASSISTANCE = 'TblCommonInformation_IsAssistance';

    /**
     * @Column(type="string")
     */
    protected $TblCommon_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_tblCommonBirthDates;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_tblCommonInformation;

    /**
     * @Column(type="string")
     */
    protected $TblCommonBirthDates_Id;
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
    protected $TblCommonBirthDates_Gender;

    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Nationality;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_Denomination;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_AssistanceActivity;
    /**
     * @Column(type="string")
     */
    protected $TblCommonInformation_IsAssistance;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Personendaten (Erweitert)';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_COMMON_REMARK, 'Personendaten: Bemerkungen');

        $this->setNameDefinition(self::TBL_COMMON_BIRTH_DATES_GENDER, 'Personendaten: Geschlecht');
        $this->setNameDefinition(self::TBL_COMMON_BIRTH_DATES_BIRTHPLACE, 'Personendaten: Geburtsort');
        $this->setNameDefinition(self::TBL_COMMON_BIRTH_DATES_BIRTHDAY, 'Personendaten: Geburtsdatum');

        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_NATIONALITY, 'Personendaten: Nationalität');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_DENOMINATION, 'Personendaten: Konfession');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_ASSISTANCE_ACTIVITY, 'Personendaten: Aktivitäten');
        $this->setNameDefinition(self::TBL_COMMON_INFORMATION_IS_ASSISTANCE, 'Personendaten: Mitarbeitsbereitschaft');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_COMMON_SERVICE_TBL_PERSON, new ViewPerson(), 'TblPerson_Id');
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Common::useService();
    }
}
