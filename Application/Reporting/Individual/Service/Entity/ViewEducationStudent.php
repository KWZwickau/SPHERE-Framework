<?php
namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewEducationStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewEducationStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    // tblYear
    const TBL_YEAR_ID = 'TblYear_Id';
    const TBL_YEAR_YEAR = 'TblYear_Year';
    const TBL_YEAR_DESCRIPTION = 'TblYear_Description';
    // TblType
    const TBL_TYPE_ID = 'TblType_Id';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';
    // tblCompany
    const TBL_COMPANY_NAME = 'TblCompany_Name';
    const TBL_COMPANY_EXTENDED_NAME = 'TblCompany_ExtendedName';
    // TblLessonStudentEducation
    const TBL_LESSON_STUDENT_EDUCATION_LEVEL = 'TblLessonStudentEducation_Level';
    // TblCourse
    const TBL_COURSE_NAME = 'TblCourse_Name';
    // tblLessonDivisionCourse (Division)
    const TBL_LESSON_DIVISION_COURSE_NAME_D = 'TblLessonDivisionCourse_Name_D';
    const TBL_LESSON_DIVISION_COURSE_DESCRIPTION_D = 'TblLessonDivisionCourse_Description_D';
    const TBL_PERSON_TEACHER_LAST_NAME_LIST = 'TblPerson_TeacherLastNameList';
    // tblLessonDivisionCourse (CoreGroup)
    const TBL_LESSON_DIVISION_COURSE_NAME_C = 'TblLessonDivisionCourse_Name_C';
    const TBL_LESSON_DIVISION_COURSE_DESCRIPTION_C = 'TblLessonDivisionCourse_Description_C';
    const TBL_PERSON_TUTOR_LAST_NAME_LIST = 'TblPerson_TutorLastNameList';

    /**
     * @return array
     */
    public static function getConstants()
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
    protected $TblLessonStudentEducation_Level;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblLessonDivisionCourse_Name_D;
    /**
     * @Column(type="string")
     */
    protected $TblLessonDivisionCourse_Description_D;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_TeacherLastNameList;
    /**
     * @Column(type="string")
     */
    protected $TblLessonDivisionCourse_Name_C;
    /**
     * @Column(type="string")
     */
    protected $TblLessonDivisionCourse_Description_C;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_TutorLastNameList;
    /**
     * @Column(type="string")
     */
    protected $TblType_Id;
    /**
     * @Column(type="string")
     */
    protected $TblType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblCourse_Name;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Id;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Year;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Description;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_YEAR_YEAR, 'Bildung: Schuljahr');
        $this->setNameDefinition(self::TBL_YEAR_DESCRIPTION, 'Bildung: Schuljahr Beschreibung');
        $this->setNameDefinition(self::TBL_TYPE_NAME, 'Bildung: Schulart');
        $this->setNameDefinition(self::TBL_COMPANY_NAME, 'Bildung: Schule');
        $this->setNameDefinition(self::TBL_COMPANY_EXTENDED_NAME, 'Bildung: Schule und Zusatz');
        $this->setNameDefinition(self::TBL_LESSON_STUDENT_EDUCATION_LEVEL, 'Bildung: Klassenstufe');
        $this->setNameDefinition(self::TBL_COURSE_NAME, 'Bildung: Bildungsgang');
        $this->setNameDefinition(self::TBL_LESSON_DIVISION_COURSE_NAME_D, 'Bildung: Klasse');
//        $this->setNameDefinition(self::TBL_LESSON_DIVISION_COURSE_DESCRIPTION_D, 'Bildung: Klassen Beschreibung');
        $this->setNameDefinition(self::TBL_PERSON_TEACHER_LAST_NAME_LIST, 'Bildung: Klassenlehrer');
        $this->setNameDefinition(self::TBL_LESSON_DIVISION_COURSE_NAME_C, 'Bildung: Stammgruppe');
//        $this->setNameDefinition(self::TBL_LESSON_DIVISION_COURSE_DESCRIPTION_C, 'Bildung: Stammgruppe Beschreibung');
        $this->setNameDefinition(self::TBL_PERSON_TUTOR_LAST_NAME_LIST, 'Bildung: Tutor');

        //GroupDefinition
        $this->setGroupDefinition('Schulverlauf', array(
            self::TBL_YEAR_YEAR,
            self::TBL_YEAR_DESCRIPTION,
            self::TBL_TYPE_NAME,
            self::TBL_COMPANY_NAME,
            self::TBL_COMPANY_EXTENDED_NAME,
            self::TBL_LESSON_STUDENT_EDUCATION_LEVEL,
            self::TBL_COURSE_NAME,
            self::TBL_LESSON_DIVISION_COURSE_NAME_D,
//            self::TBL_LESSON_DIVISION_COURSE_DESCRIPTION_D,
            self::TBL_PERSON_TEACHER_LAST_NAME_LIST,
            self::TBL_LESSON_DIVISION_COURSE_NAME_C,
//            self::TBL_LESSON_DIVISION_COURSE_DESCRIPTION_C,
            self::TBL_PERSON_TUTOR_LAST_NAME_LIST,
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
//            case self::TBL_LEVEL_ID:
//                // Test Address By Student
//                $Data = array();
//                $tblLevelList = Division::useService()->getLevelAll();
//                if($tblLevelList){
//                    foreach($tblLevelList as $tblLevel){
//                        // nur richtige Klassenstufen anzeigen
//                        if(!$tblLevel->getIsChecked()){
//                            $Type = '';
//                            // Schulart der Klassenstufe zusätzlich anzeigen
//                            if(($tblType = $tblLevel->getServiceTblType())){
//                                $Type = $tblType->getName();
//                            }
//                            $Data[$tblLevel->getId()] = $tblLevel->getName().' '.$Type;
//                        }
//                    }
//                }
//                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount, false);
//                break;
            case self::TBL_TYPE_NAME:
                $Data = Type::useService()->getPropertyList(new TblType(), TblType::ATTR_NAME);
                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, true);
                break;
            case self::TBL_YEAR_YEAR:
                $Data = Term::useService()->getPropertyList( new TblYear(), TblYear::ATTR_YEAR );
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Placeholder, $Label, $Icon,
                    $doResetCount);
                break;
//            case self::TBL_SUBJECT_NAME:
//                $Data = Subject::useService()->getPropertyList(new TblSubject(), TblSubject::ATTR_NAME);
//                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Placeholder, $Label, $Icon,
//                    $doResetCount);
//                break;
//            case self::TBL_SUBJECT_ACRONYM:
//                $Data = Subject::useService()->getPropertyList(new TblSubject(), TblSubject::ATTR_ACRONYM);
//                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
//                break;
//            case self::TBL_LEVEL_IS_CHECKED:
//                $Data = array( 0 => 'Nein', 1 => 'Ja' );
//                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount, false );
//                break;
            case self::TBL_COMPANY_NAME:
                $Data = array();
                if(($tblSchoolList = School::useService()->getSchoolAll())){
                    foreach($tblSchoolList as $tblSchool){
                        if(($tblCompany = $tblSchool->getServiceTblCompany())){
                            $Data[] = $tblCompany->getName();
                        }
                    }
                }
                if(!empty($Data)){
                    $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, true);
                } else {
                    $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                }
                break;
            case self::TBL_COMPANY_EXTENDED_NAME:
                $Data = array();
                if(($tblSchoolList = School::useService()->getSchoolAll())){
                    foreach($tblSchoolList as $tblSchool){
                        if(($tblCompany = $tblSchool->getServiceTblCompany())){
                            $Data[] = $tblCompany->getDisplayName();
                        }
                    }
                }
                if(!empty($Data)){
                    $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, true);
                } else {
                    $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                }
                break;

            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
        }
        return $Field;
    }
}
