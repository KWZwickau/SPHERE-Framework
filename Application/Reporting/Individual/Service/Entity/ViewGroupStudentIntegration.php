<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupStudentIntegration")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentIntegration extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    // Integration
    const TBL_STUDENT_FOCUS_TYPE_NAME_LIST = 'TblStudentFocusType_NameList';
    const TBL_STUDENT_FOCUS_TYPE_MAIN_FOCUS = 'TblStudentFocusType_MainFocus';
    const TBL_STUDENT_DISORDER_TYPE_NAME_LIST = 'TblStudentDisorderType_NameList';
    const TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE = 'TblStudentIntegration_CoachingRequestDate';
    const TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE = 'TblStudentIntegration_CoachingCounselDate';
    const TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE = 'TblStudentIntegration_CoachingDecisionDate';
    const TBL_STUDENT_INTEGRATION_COACHING_REQUIRED = 'TblStudentIntegration_CoachingRequired';
    const TBL_STUDENT_INTEGRATION_COACHING_TIME = 'TblStudentIntegration_CoachingTime';
    const TBL_STUDENT_INTEGRATION_COACHING_REMARK = 'TblStudentIntegration_CoachingRemark';
//    const TBL_SALUTATION_SALUTATION_COACH = 'TblSalutation_Salutation_Coach';
    const TBL_PERSON_COACH = 'TblPerson_Coach';

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
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentFocusType_NameList;
    /**
     * @Column(type="string")
     */
    protected $TblStudentFocusType_MainFocus;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorderType_NameList;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRequestDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingCounselDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingDecisionDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRequired;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingTime;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRemark;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblSalutation_Salutation_Coach;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Coach;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_STUDENT_FOCUS_TYPE_MAIN_FOCUS, 'Integration: Hauptschwerpunkt');
        $this->setNameDefinition(self::TBL_STUDENT_FOCUS_TYPE_NAME_LIST, 'Integration: Schwerpunkte');
        $this->setNameDefinition(self::TBL_STUDENT_DISORDER_TYPE_NAME_LIST, 'Integration: Teilleistungsstörungen');

        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE, 'Integration: Datum Förderantrag Beratung');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE, 'Integration: Datum Förderantrag');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE, 'Integration: Datum Förderbescheid SBA');

        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REQUIRED, 'Integration: Förderbedarf');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_TIME, 'Integration: Stundenbedarf pro Woche');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REMARK, 'Integration: Bemerkung');
//        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_COACH, 'Integration: Anrede Schulbegleitung');
        $this->setNameDefinition(self::TBL_PERSON_COACH, 'Integration: Schulbegleitung');

//        //GroupDefinition
        $this->setGroupDefinition('&nbsp;', array(
            self::TBL_STUDENT_FOCUS_TYPE_MAIN_FOCUS,
            self::TBL_STUDENT_FOCUS_TYPE_NAME_LIST,
            self::TBL_STUDENT_DISORDER_TYPE_NAME_LIST,
            self::TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE,
            self::TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE,
            self::TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE,
            self::TBL_STUDENT_INTEGRATION_COACHING_REQUIRED,
            self::TBL_STUDENT_INTEGRATION_COACHING_TIME,
            self::TBL_STUDENT_INTEGRATION_COACHING_REMARK,
//            self::TBL_SALUTATION_SALUTATION_COACH,
            self::TBL_PERSON_COACH,
        ));
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
            case self::TBL_STUDENT_FOCUS_TYPE_MAIN_FOCUS:
                $Data = Common::useService()->getPropertyList(new TblStudentFocusType(), TblStudentFocusType::ATTR_NAME);
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_STUDENT_FOCUS_TYPE_NAME_LIST:
                $Data = Common::useService()->getPropertyList(new TblStudentFocusType(), TblStudentFocusType::ATTR_NAME);
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_STUDENT_DISORDER_TYPE_NAME_LIST:
                $Data = Common::useService()->getPropertyList(new TblStudentDisorderType(), TblStudentDisorderType::ATTR_NAME);
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_STUDENT_INTEGRATION_COACHING_REQUIRED:
                $Data[1] = 'Ja';
                $Data[2] = 'Nein';
                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
