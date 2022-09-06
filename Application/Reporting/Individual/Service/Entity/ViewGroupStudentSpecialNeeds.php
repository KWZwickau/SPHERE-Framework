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
 * @Table(name="viewGroupStudentSpecialNeeds")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentSpecialNeeds extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    // SpecialNeeds
    const TBL_STUDENT_SPECIAL_NEEDS_SBJ = 'TblStudentSpecialNeeds_SBJ';
    const TBL_STUDENT_SPECIAL_NEEDS_IS_HEAVY_MULTIPLE_HANDICAPPED = 'TblStudentSpecialNeeds_IsHeavyMultipleHandicapped';
    const TBL_STUDENT_SPECIAL_NEEDS_REMARK_HEAVY_MULTIPLE_HANDICAPPED = 'TblStudentSpecialNeeds_RemarkHeavyMultipleHandicapped';
    const TBL_STUDENT_SPECIAL_NEEDS_DEGREE_OF_HANDICAP = 'TblStudentSpecialNeeds_DegreeOfHandicap';
    const TBL_STUDENT_SPECIAL_NEEDS_SIGN = 'TblStudentSpecialNeeds_Sign';
    const TBL_STUDENT_SPECIAL_NEEDS_VALID_TO = 'TblStudentSpecialNeeds_ValidTo';
    const TBL_STUDENT_SPECIAL_NEEDS_FACTOR_HANDICAPPED_SCHOOL = 'TblStudentSpecialNeeds_FactorHandicappedSchool';
    const TBL_STUDENT_SPECIAL_NEEDS_FACTOR_HANDICAPPED_REGIONAL_AUTHORITIES = 'TblStudentSpecialNeeds_FactorHandicappedRegionalAuthorities';
    // Special Needs Level
    const TBL_STUDENT_SPECIAL_NEEDS_LEVEL_NAME = 'TblStudentSpecialNeedsLevel_Name';

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
    protected $TblStudentSpecialNeeds_SBJ;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_IsHeavyMultipleHandicapped;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_RemarkHeavyMultipleHandicapped;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_DegreeOfHandicap;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_Sign;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_ValidTo;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_FactorHandicappedSchool;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeeds_FactorHandicappedRegionalAuthorities;
    /**
     * @Column(type="string")
     */
    protected $TblStudentSpecialNeedsLevel_Name;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        // NameDefinition
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_SBJ, 'Förderschüler: SBJ');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_IS_HEAVY_MULTIPLE_HANDICAPPED, 'Förderschüler: schwerstmehrfachbehindert');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_REMARK_HEAVY_MULTIPLE_HANDICAPPED, 'Förderschüler: Bemerkung');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_DEGREE_OF_HANDICAP, 'Förderschüler: GdB');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_SIGN, 'Förderschüler: Merkzeichen');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_VALID_TO, 'Förderschüler: Gültig bis');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_FACTOR_HANDICAPPED_SCHOOL, 'Förderschüler: Erhöhungsfaktor Schule');
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_FACTOR_HANDICAPPED_REGIONAL_AUTHORITIES, 'Förderschüler: Erhöhungsfaktor LaSuB');
        // Stufe
        $this->setNameDefinition(self::TBL_STUDENT_SPECIAL_NEEDS_LEVEL_NAME, 'Förderschüler: Stufe');

//        //GroupDefinition
        $this->setGroupDefinition('&nbsp;', array(
            self::TBL_STUDENT_SPECIAL_NEEDS_SBJ,
            self::TBL_STUDENT_SPECIAL_NEEDS_IS_HEAVY_MULTIPLE_HANDICAPPED,
            self::TBL_STUDENT_SPECIAL_NEEDS_REMARK_HEAVY_MULTIPLE_HANDICAPPED,
            self::TBL_STUDENT_SPECIAL_NEEDS_DEGREE_OF_HANDICAP,
            self::TBL_STUDENT_SPECIAL_NEEDS_SIGN,
            self::TBL_STUDENT_SPECIAL_NEEDS_VALID_TO,
            self::TBL_STUDENT_SPECIAL_NEEDS_FACTOR_HANDICAPPED_SCHOOL,
            self::TBL_STUDENT_SPECIAL_NEEDS_FACTOR_HANDICAPPED_REGIONAL_AUTHORITIES,
            self::TBL_STUDENT_SPECIAL_NEEDS_LEVEL_NAME,
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
//            case self::TBL_STUDENT_FOCUS_TYPE_MAIN_FOCUS:
//                $Data = Common::useService()->getPropertyList(new TblStudentFocusType(), TblStudentFocusType::ATTR_NAME);
//                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
//                break;
//            case self::TBL_STUDENT_FOCUS_TYPE_NAME_LIST:
//                $Data = Common::useService()->getPropertyList(new TblStudentFocusType(), TblStudentFocusType::ATTR_NAME);
//                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
//                break;
//            case self::TBL_STUDENT_DISORDER_TYPE_NAME_LIST:
//                $Data = Common::useService()->getPropertyList(new TblStudentDisorderType(), TblStudentDisorderType::ATTR_NAME);
//                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Label, $Icon, $doResetCount);
//                break;
            case self::TBL_STUDENT_SPECIAL_NEEDS_IS_HEAVY_MULTIPLE_HANDICAPPED:
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
