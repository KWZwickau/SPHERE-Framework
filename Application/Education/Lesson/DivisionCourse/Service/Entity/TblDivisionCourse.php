<?php
namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonDivisionCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCourse extends Element
{
    const ATTR_TBL_TYPE = 'tblLessonDivisionCourseType';
    const ATTR_NAME = 'Name';
    const SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_IS_SHOWN_IN_PERSON_DATA = 'IsShownInPersonData';
    const ATTR_IS_REPORTING = 'IsReporting';

    const ATTR_MIGRATE_GROUP_ID = 'MigrateGroupId';
    const ATTR_MIGRATE_SEK_COURSE = 'MigrateSekCourse';

    /**
     * muss null sein für SelectBox
     *
     * @Column(type="bigint")
     */
    protected ?int $tblLessonDivisionCourseType = null;

    /**
     *  muss null sein für SelectBox
     *
     * @Column(type="bigint")
     */
    protected ?int $serviceTblYear = null;

    /**
     * @Column(type="string")
     */
    protected string $Name = '';

    /**
     * @Column(type="string")
     */
    protected string $Description = '';

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject = null;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsShownInPersonData = false;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsReporting = false;

//    /**
//     * @Column(type="boolean")
//     */
//    protected bool $IsUcs = false;

    /**
     * @Column(type="bigint")
     */
    protected ?int $MigrateGroupId = null;

    /**
     * @Column(type="string")
     */
    protected ?string $MigrateSekCourse = null;

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param TblSubject|null $tblSubject
     * @param int|null $migrateGroupId
     * @param string|null $migrateSekCourse
     *
     * @return TblDivisionCourse
     */
    public static function withParameter(
        TblDivisionCourseType $tblType, TblYear $tblYear, string $name, string $description,
        bool $isShownInPersonData = false, bool $isReporting = false, ?TblSubject $tblSubject = null,
        ?int $migrateGroupId = null, ?string $migrateSekCourse = null
    ): TblDivisionCourse
    {
        // php erlaubt leider keine mehrfach Konstruktoren :(
        $instance = new self();

        $instance->tblLessonDivisionCourseType = $tblType->getId();
        $instance->serviceTblYear = $tblYear->getId();
        $instance->Name = $name;
        $instance->Description = $description;
        $instance->IsShownInPersonData = $isShownInPersonData;
        $instance->IsReporting = $isReporting;
        //        $instance->IsUcs = $isUcs;
        $instance->setServiceTblSubject($tblSubject);
        $instance->MigrateGroupId = $migrateGroupId;
        $instance->MigrateSekCourse = $migrateSekCourse;

        return  $instance;
    }

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $Name
     * @param string $Description
     * @param int $Id
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     *
     * @return TblDivisionCourse
     */
    public static function withParameterAndId(TblDivisionCourseType $tblType, TblYear $tblYear, string $Name, string $Description, int $Id,
        bool $isShownInPersonData = false, bool $isReporting = false): TblDivisionCourse
    {
        $instance = self::withParameter($tblType, $tblYear, $Name, $Description, $isShownInPersonData, $isReporting);
        $instance->Id = $Id;

        return  $instance;
    }



    /**
     * @return false|TblDivisionCourseType
     */
    public function getType()
    {
        return DivisionCourse::useService()->getDivisionCourseTypeById($this->tblLessonDivisionCourseType);
    }

    /**
     * @param TblDivisionCourseType $tblType
     */
    public function setType(TblDivisionCourseType $tblType)
    {
        $this->tblLessonDivisionCourseType = $tblType->getId();
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear)
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName(string $Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        return $this->serviceTblSubject ? Subject::useService()->getSubjectById($this->serviceTblSubject) : false;
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject ? $tblSubject->getId() : null;
    }

    /**
     * @return bool
     */
    public function getIsShownInPersonData(): bool
    {
        return $this->IsShownInPersonData;
    }

    /**
     * @param bool $IsShownInPersonData
     */
    public function setIsShownInPersonData(bool $IsShownInPersonData): void
    {
        $this->IsShownInPersonData = $IsShownInPersonData;
    }

    /**
     * @return bool
     */
    public function getIsReporting(): bool
    {
        return $this->IsReporting;
    }

    /**
     * @param bool $IsReporting
     */
    public function setIsReporting(bool $IsReporting): void
    {
        $this->IsReporting = $IsReporting;
    }

//    /**
//     * @return bool
//     */
//    public function getIsUcs(): bool
//    {
//        return $this->IsUcs;
//    }
//
//    /**
//     * @param bool $IsUcs
//     */
//    public function setIsUcs(bool $IsUcs): void
//    {
//        $this->IsUcs = $IsUcs;
//    }

    /**
     * @return string
     */
    public function getTypeIdentifier(): string
    {
        if (($tblType = $this->getType())) {
            return $tblType->getIdentifier();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        if (($tblType = $this->getType())) {
            return $tblType->getName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getYearName(): string
    {
        if (($tblYear = $this->getServiceTblYear())) {
            return $tblYear->getDisplayName();
        }

        return '';
    }

    /**
     * @param bool $isPlural
     *
     * @return string
     */
    public function getDivisionTeacherName(bool $isPlural = true): string
    {
        switch ($this->getTypeName()) {
            case 'Klasse': return 'Klassenlehrer';
            case 'Stammgruppe': return $isPlural ? 'Tutoren/Mentoren' : 'Tudor/Mentor';
            case 'Unterrichtsgruppe':
            default: return 'Gruppenleiter';
        }
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblPerson[]
     */
    public function getStudents(bool $withInActive = false) {
        if ($this->getTypeIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE || $this->getTypeIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE) {
            $tblPersonList = array();
            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListBySubjectDivisionCourse($this))) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblPersonTemp = $tblStudentSubject->getServiceTblPerson())) {
                        $tblPersonList[$tblPersonTemp->getId()] = $tblPersonTemp;
                    }
                }
            }

            return empty($tblPersonList) ? false : $tblPersonList;
        } else {
            return DivisionCourse::useService()->getDivisionCourseMemberListBy($this, TblDivisionCourseMemberType::TYPE_STUDENT, $withInActive);
        }
    }

    /**
     * @return false|TblPerson[]
     */
    public function getCustody()
    {
        return DivisionCourse::useService()->getDivisionCourseMemberListBy($this, TblDivisionCourseMemberType::TYPE_CUSTODY);
    }

    /**
     * @param bool $withInActive
     * @param bool $isResultPersonList
     *
     * @return false|TblDivisionCourseMember[]|TblPerson[]
     */
    public function getStudentsWithSubCourses(bool $withInActive = false, bool $isResultPersonList = true)
    {
        if ($this->getTypeIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE || $this->getTypeIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE) {
            $tblPersonList = array();
            if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListBySubjectDivisionCourse($this))) {
                foreach ($tblStudentSubjectList as $tblStudentSubject) {
                    if (($tblPersonTemp = $tblStudentSubject->getServiceTblPerson())) {
                        $tblPersonList[$tblPersonTemp->getId()] = $tblPersonTemp;
                    }
                }
            }

            return empty($tblPersonList) ? false : $tblPersonList;
        } else {
            return DivisionCourse::useService()->getStudentListBy($this, $withInActive, $isResultPersonList);
        }
    }

    /**
     * @return int
     */
    public function getCountStudents(): int
    {
        return DivisionCourse::useService()->getCountStudentByDivisionCourse($this);
    }

    /**
     * @return int
     */
    public function getCountInActiveStudents(): int
    {
        return DivisionCourse::useService()->getCountInActiveStudentByDivisionCourse($this);
    }

    /**
     * Name (Beschreibung)
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->getName() . (($description = $this->getDescription()) ? ' (' . $description . ')' : '');
    }

    /**
     * @param string $separator
     *
     * @return string
     */
    public function getDivisionTeacherNameListString(string $separator = '<br/>'): string
    {
        return DivisionCourse::useService()->getDivisionTeacherNameListString($this, $separator);
    }

    /**
     * @return false|TblPerson[]
     */
    public function getDivisionTeacherList()
    {
        return DivisionCourse::useService()->getDivisionCourseMemberListBy($this, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER);
    }

    /**
     * @return false|TblPerson
     */
    public function getFirstDivisionTeacher()
    {
        if (($tempList = $this->getDivisionTeacherList())) {
            return current($tempList);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSubjectName(): string
    {
        return ($tblSubject = $this->getServiceTblSubject()) ? $tblSubject->getDisplayName() : '';
    }

    /**
     * @param bool $isString
     * @return false|Type[]|string
     */
    public function getSchoolTypeListFromStudents(bool $isString = false)
    {
        return DivisionCourse::useService()->getSchoolTypeListByDivisionCourse($this, $isString);
    }

    /**
     * @param bool $isString
     * @return false|Type[]|string
     */
    public function getCompanyListFromStudents(bool $isString = false)
    {
        return DivisionCourse::useService()->getCompanyListByDivisionCourse($this, $isString);
    }

    /**
     * @return bool
     */
    public function getHasSaturdayLessons(): bool
    {
        return DivisionCourse::useService()->getHasSaturdayLessonsByDivisionCourse($this);
    }

    /**
     * @return bool
     */
    public function getIsDivisionOrCoreGroup(): bool
    {
        return $this->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $this->getTypeIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP;
    }
}