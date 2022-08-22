<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Masern\Service\Entity\TblPersonMasern;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupTeacher")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupTeacher extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_TEACHER_ACRONYM = 'TblTeacher_Acronym';

    const TBL_PERSON_MASERN_MASERN_DATE = 'TblPersonMasern_MasernDate';
    const TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE = 'TblStudentMedicalRecord_MasernDocumentType';
    const TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE = 'TblStudentMedicalRecord_MasernCreatorType';

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
    protected $TblTeacher_Acronym;
    /**
     * @Column(type="string")
     */
    protected $TblPersonMasern_MasernDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_MasernDocumentType;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_MasernCreatorType;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_TEACHER_ACRONYM, 'Mitarbeiter: Kürzel');
        $this->setNameDefinition(self::TBL_PERSON_MASERN_MASERN_DATE, 'Masern: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE, 'Masern: Art der Bescheinigung');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE, 'Masern: Bescheinigung durch');

//        //GroupDefinition
        $this->setGroupDefinition('Zusatz', array(
            self::TBL_TEACHER_ACRONYM,
        ));
        //GroupDefinition
        $this->setGroupDefinition('Masern', array(
            self::TBL_PERSON_MASERN_MASERN_DATE,
            self::TBL_STUDENT_MEDICAL_RECORD_MASERN_DOCUMENT_TYPE,
            self::TBL_STUDENT_MEDICAL_RECORD_MASERN_CREATOR_TYPE,
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
            case self::TBL_TEACHER_ACRONYM:
                // old version: all name from City
                $Data = Teacher::useService()->getPropertyList( new TblTeacher(), TblTeacher::ATTR_ACRONYM );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_PERSON_MASERN_MASERN_DATE:
                $Data = Common::useService()->getPropertyList( new TblPersonMasern(), TblPersonMasern::ATTR_MASERN_DATE );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
