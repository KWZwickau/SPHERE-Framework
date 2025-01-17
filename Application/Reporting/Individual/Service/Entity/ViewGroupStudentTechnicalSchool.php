<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Type\Service\Entity\TblCategory;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupStudentTechnicalSchool")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentTechnicalSchool extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    // SchoolDiploma
    const TBL_SCHOOL_DIPLOMA_NAME = 'TblSchoolDiploma_Name';
    // SchoolType
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_ID = 'TblType_Id';
    // TechnicalDiploma
    const TBL_TECHNICAL_DIPLOMA_NAME = 'TblTechnicalDiploma_Name';
    const TBL_TECHNICAL_DIPLOMA_ID = 'TblTechnicalDiploma_Id';
    // TechnicalType
    const TBL_TECHNICAL_TYPE_NAME = 'TblTechnicalType_Name';
    const TBL_TECHNICAL_TYPE_ID = 'TblTechnicalType_Id';

    // TechnicalCourse
    const TBL_TECHNICAL_COURSE_NAME = 'TblTechnicalCourse_Name';
    // StudentTechnicalSchool
    const TBL_STUDENT_TECHNICAL_SCHOOL_PRAXIS_LESSONS = 'TblStudentTechnicalSchool_PraxisLessons';
    const TBL_STUDENT_TECHNICAL_SCHOOL_DURATION_OF_TRAINING = 'TblStudentTechnicalSchool_DurationOfTraining';
    const TBL_STUDENT_TECHNICAL_SCHOOL_REMARK = 'TblStudentTechnicalSchool_Remark';
    const TBL_STUDENT_TECHNICAL_SCHOOL_YEAR_OF_SCHOOL_DIPLOMA = 'TblStudentTechnicalSchool_YearOfSchoolDiploma';
    const TBL_STUDENT_TECHNICAL_SCHOOL_YEAR_OF_TECHNICAL_DIPLOMA = 'TblStudentTechnicalSchool_YearOfTechnicalDiploma';
    const TBL_STUDENT_TECHNICAL_SCHOOL_HAS_FINANCIAL_AID = 'TblStudentTechnicalSchool_HasFinancialAid';
    const TBL_STUDENT_TECHNICAL_SCHOOL_FINANCIAL_AID_APPLICATION_YEAR = 'TblStudentTechnicalSchool_FinancialAidApplicationYear';
    const TBL_STUDENT_TECHNICAL_SCHOOL_FINANCIAL_AID_BUREAU = 'TblStudentTechnicalSchool_FinancialAidBureau';
    // TechnicalSubjectArea
    const TBL_TECHNICAL_SUBJECT_AREA_NAME = 'TblTechnicalSubjectArea_Name';
    const TBL_TECHNICAL_SUBJECT_AREA_ACRONYM = 'TblTechnicalSubjectArea_Acronym';
    // StudentTensOfLesson
    const TBLSTUDENTTENSOFLESSON_NAME = 'TblStudentTensOfLesson_Name';
    // StudentTrainingStatus
    const TBLSTUDENTTRAININGSTATUS_NAME = 'TblStudentTrainingStatus_Name';

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
    protected $TblTechnicalCourse_Name;
    /**
     * @Column(type="string")
     */
    protected $TblSchoolDiploma_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblTechnicalDiploma_Name;
    /**
     * @Column(type="string")
     */
    protected $TblTechnicalDiploma_Id;
    /**
     * @Column(type="string")
     */
    protected $TblTechnicalType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblTechnicalType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_PraxisLessons;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_DurationOfTraining;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_YearOfSchoolDiploma;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_YearOfTechnicalDiploma;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_HasFinancialAid;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_FinancialAidApplicationYear;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTechnicalSchool_FinancialAidBureau;
    /**
     * @Column(type="string")
     */
    protected $TblTechnicalSubjectArea_Name;
    /**
     * @Column(type="string")
     */
    protected $TblTechnicalSubjectArea_Acronym;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTensOfLesson_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTrainingStatus_Name;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_SCHOOL_DIPLOMA_NAME, 'Aufnahme BS: höchster Abschluss allgemeinb Schule');
//        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Aufnahme BS: Schulart allgemeinb Schule');
        $this->setNameDefinition(self::TBL_TYPE_ID, 'Aufnahme BS: Schulart allgemeinb Schule');
        $this->setNameDefinition(self::TBL_TECHNICAL_DIPLOMA_ID, 'Aufnahme BS: höchster Abschluss berufsb Schule');
//        $this->setNameDefinition(self::TBL_TECHNICAL_TYPE_NAME, 'Aufnahme BS: Schulart berufsb Schule');
        $this->setNameDefinition(self::TBL_TECHNICAL_TYPE_ID, 'Aufnahme BS: Schulart berufsb Schule');

        $this->setNameDefinition(self::TBL_TECHNICAL_COURSE_NAME, 'Allgemeines BS: Bildungsgang / Berufsbezeichnung / Ausbildung');
        $this->setNameDefinition(self::TBLSTUDENTTENSOFLESSON_NAME, 'Allgemeines BS: Zeitform');
        $this->setNameDefinition(self::TBLSTUDENTTRAININGSTATUS_NAME, 'Allgemeines BS: Ausbildungsstatus');

        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_DURATION_OF_TRAINING, 'Allgemeines BS: Ausbildungsdauer');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_PRAXIS_LESSONS, 'Allgemeines BS: Praxisstunden');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_REMARK, 'Allgemeines BS: Bemerkung');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_YEAR_OF_SCHOOL_DIPLOMA, 'Allgemeines BS: Abschlussjahr allgemeinbildend');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_YEAR_OF_TECHNICAL_DIPLOMA, 'Allgemeines BS: Abschlussjahr berufsbildend');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_HAS_FINANCIAL_AID, 'Allgemeines BS: Bafög');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_FINANCIAL_AID_APPLICATION_YEAR, 'Allgemeines BS: Beantragungsjahr');
        $this->setNameDefinition(self::TBL_STUDENT_TECHNICAL_SCHOOL_FINANCIAL_AID_BUREAU, 'Allgemeines BS: Amt');

        $this->setNameDefinition(self::TBL_TECHNICAL_SUBJECT_AREA_NAME, 'Allgemeines BS: Fachrichtung');
        $this->setNameDefinition(self::TBL_TECHNICAL_SUBJECT_AREA_ACRONYM, 'Allgemeines BS: Abkürzung Fachrichtung');

        //GroupDefinition
        $this->setGroupDefinition('Schüler - Aufnahme BS', array(
            self::TBL_SCHOOL_DIPLOMA_NAME,
            self::TBL_TYPE_ID,
            self::TBL_TECHNICAL_DIPLOMA_ID,
            self::TBL_TECHNICAL_TYPE_ID,
        ));
        $this->setGroupDefinition('Allgemeines BS', array(
            self::TBL_TECHNICAL_COURSE_NAME,
            self::TBLSTUDENTTENSOFLESSON_NAME,
            self::TBLSTUDENTTRAININGSTATUS_NAME,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_DURATION_OF_TRAINING,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_PRAXIS_LESSONS,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_REMARK,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_YEAR_OF_SCHOOL_DIPLOMA,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_YEAR_OF_TECHNICAL_DIPLOMA,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_HAS_FINANCIAL_AID,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_FINANCIAL_AID_APPLICATION_YEAR,
            self::TBL_STUDENT_TECHNICAL_SCHOOL_FINANCIAL_AID_BUREAU,
            self::TBL_TECHNICAL_SUBJECT_AREA_NAME,
            self::TBL_TECHNICAL_SUBJECT_AREA_ACRONYM,
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
            case self::TBL_STUDENT_TECHNICAL_SCHOOL_HAS_FINANCIAL_AID:
                $Data = array( 0 => 'Nein', 1 => 'Ja' );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_SCHOOL_DIPLOMA_NAME:
                $Data = Course::useService()->getPropertyList(new TblSchoolDiploma(), TblSchoolDiploma::ATTR_NAME);
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_TYPE_ID:
                $tblTypeCommonAll = Type::useService()->getTypeAllByCategory(Type::useService()->getCategoryByIdentifier(TblCategory::COMMON));
                $tblTypeSecondCourseAll = Type::useService()->getTypeAllByCategory(Type::useService()->getCategoryByIdentifier(TblCategory::SECOND_COURSE));
                $Data = array();
                if($tblTypeCommonAll){
                    foreach($tblTypeCommonAll as $tblTypeCommon) {
                        $Data[$tblTypeCommon->getId()] = $tblTypeCommon->getName();
                    }
                }
                if($tblTypeSecondCourseAll){
                    foreach($tblTypeSecondCourseAll as $tblTypeSecondCourse) {
                        $Data[$tblTypeSecondCourse->getId()] = $tblTypeSecondCourse->getName();
                    }
                }
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, false);
                break;
            case self::TBL_TECHNICAL_DIPLOMA_ID:
                $Data = array();
                if(($tblTechnicalDiplomaAll = Course::useService()->getTechnicalDiplomaAll())){
                    foreach($tblTechnicalDiplomaAll as $tblTechnicalDiploma){
                        $Data[$tblTechnicalDiploma->getId()] = $tblTechnicalDiploma->getName();
                    }
                }
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, false);
                break;
            case self::TBL_TECHNICAL_TYPE_ID:
                $Data = array();
                if(($tblTypeTechnicalAll = Type::useService()->getTypeAllByCategory(Type::useService()->getCategoryByIdentifier(TblCategory::TECHNICAL)))){
                    foreach($tblTypeTechnicalAll as $tblType){
                        $Data[$tblType->getId()] = $tblType->getName();
                    }
                }
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, false);
                break;
            case self::TBL_TECHNICAL_COURSE_NAME:
                $Data = array();
                if(($tblTechnicalCourseAll = Course::useService()->getTechnicalCourseAll())){
                    foreach($tblTechnicalCourseAll as $tblTechnicalCourse){
                        $Data[] = $tblTechnicalCourse->getName();
                    }
                }
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBLSTUDENTTENSOFLESSON_NAME:
                $Data = array();
                if(($tblStudentTenseOfLessonAll = Student::useService()->getStudentTenseOfLessonAll())){
                    foreach($tblStudentTenseOfLessonAll as $tblStudentTenseOfLesson){
                        $Data[] = $tblStudentTenseOfLesson->getName();
                    }
                }
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            case self::TBLSTUDENTTRAININGSTATUS_NAME:
                $Data = array();
                if(($tblStudentTrainingStatusAll = Student::useService()->getStudentTrainingStatusAll())){
                    foreach($tblStudentTrainingStatusAll as $tblStudentTrainingStatus){
                        $Data[] = $tblStudentTrainingStatus->getName();
                    }
                }
                $Field = parent::getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
