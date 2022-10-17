<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonSubjectTable")
 * @Cache(usage="READ_ONLY")
 */
class TblSubjectTable extends Element
{
    const ATTR_SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_LEVEL = 'Level';
    const ATTR_RANKING = 'Ranking';
    const ATTR_STUDENT_META_IDENTIFIER = 'StudentMetaIdentifier';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';

    const SUBJECT_FOREIGN_LANGUAGE_1_Id = -1;
    const SUBJECT_FOREIGN_LANGUAGE_2_Id = -2;
    const SUBJECT_FOREIGN_LANGUAGE_3_Id = -3;
    const SUBJECT_FOREIGN_LANGUAGE_4_Id = -4;
    const SUBJECT_RELIGION = -5;
    const SUBJECT_PROFILE = -6;
    const SUBJECT_ORIENTATION = -7;
    const SUBJECT_ELECTIVE = -8;

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;

    /**
     * @Column(type="integer")
     */
    protected int $Level;

    /**
     * @Column(type="string")
     */
    protected string $TypeName;

    /**
     * @Column(type="integer")
     */
    protected int $Ranking;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject = null;

    /**
     * @Column(type="string")
     */
    protected string $StudentMetaIdentifier;

    /**
     * @Column(type="boolean")
     */
    protected bool $HasGrading;

    /**
     * @Column(type="integer")
     */
    protected ?int $HoursPerWeek  = null;

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param string $typeName
     * @param int $ranking
     * @param int|null $hoursPerWeek
     * @param string $studentMetaIdentifier
     * @param bool $hasGrading
     *
     * @return TblSubjectTable
     */
    public static function withParameter(TblType $tblSchoolType, int $level, ?TblSubject $tblSubject, string $typeName, int $ranking, ?int $hoursPerWeek,
        string $studentMetaIdentifier = '', bool $hasGrading = true): TblSubjectTable
    {
        $instance = new self();

        $instance->setServiceTblSchoolType($tblSchoolType);
        $instance->setLevel($level);
        $instance->setServiceTblSubject($tblSubject);
        $instance->setTypeName($typeName);
        $instance->setRanking($ranking);
        $instance->setHoursPerWeek($hoursPerWeek);
        $instance->setStudentMetaIdentifier($studentMetaIdentifier);
        $instance->setHasGrading($hasGrading);

        return  $instance;
    }

    /**
     * @return false|TblType
     */
    public function getServiceTblSchoolType()
    {
        return Type::useService()->getTypeById($this->serviceTblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType): void
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->Level;
    }

    /**
     * @param int $Level
     */
    public function setLevel(int $Level): void
    {
        $this->Level = $Level;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return $this->TypeName;
    }

    /**
     * @param string $TypeName
     */
    public function setTypeName(string $TypeName): void
    {
        $this->TypeName = $TypeName;
    }

    /**
     * @return int
     */
    public function getRanking(): int
    {
        return $this->Ranking;
    }

    /**
     * @param int $Ranking
     */
    public function setRanking(int $Ranking): void
    {
        $this->Ranking = $Ranking;
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param ?TblSubject $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject ? $tblSubject->getId() : null;
    }

    /**
     * @return string
     */
    public function getStudentMetaIdentifier(): string
    {
        return $this->StudentMetaIdentifier;
    }

    /**
     * @param string $StudentMetaIdentifier
     */
    public function setStudentMetaIdentifier(string $StudentMetaIdentifier): void
    {
        $this->StudentMetaIdentifier = $StudentMetaIdentifier;
    }

    /**
     * @return bool
     */
    public function getHasGrading(): bool
    {
        return $this->HasGrading;
    }

    /**
     * @param bool $HasGrading
     */
    public function setHasGrading(bool $HasGrading): void
    {
        $this->HasGrading = $HasGrading;
    }

    /**
     * @return int|null
     */
    public function getHoursPerWeek(): ?int
    {
        return $this->HoursPerWeek;
    }

    /**
     * @param int|null $HoursPerWeek
     */
    public function setHoursPerWeek(?int $HoursPerWeek): void
    {
        $this->HoursPerWeek = $HoursPerWeek;
    }

    /**
     * @return int
     */
    public function getSubjectId(): int
    {
        if (($tblSubject = $this->getServiceTblSubject())) {
            return $tblSubject->getId();
        }

        switch ($this->getStudentMetaIdentifier()) {
            case 'FOREIGN_LANGUAGE_1': return self::SUBJECT_FOREIGN_LANGUAGE_1_Id;
            case 'FOREIGN_LANGUAGE_2': return self::SUBJECT_FOREIGN_LANGUAGE_2_Id;
            case 'FOREIGN_LANGUAGE_3': return self::SUBJECT_FOREIGN_LANGUAGE_3_Id;
            case 'FOREIGN_LANGUAGE_4': return self::SUBJECT_FOREIGN_LANGUAGE_4_Id;
            case 'RELIGION': return self::SUBJECT_RELIGION;
            case 'PROFILE': return self::SUBJECT_PROFILE;
            case 'ORIENTATION': return self::SUBJECT_ORIENTATION;
            case 'ELECTIVE': return self::SUBJECT_ELECTIVE;
            default: return 0;
        }
    }

    /**
     * @return string
     */
    public function getSubjectName(): string
    {
        if (($tblSubject = $this->getServiceTblSubject())) {
            return $tblSubject->getName();
        }

        switch ($this->getStudentMetaIdentifier()) {
            case 'FOREIGN_LANGUAGE_1': return '1. Fremdsprache';
            case 'FOREIGN_LANGUAGE_2': return '2. Fremdsprache';
            case 'FOREIGN_LANGUAGE_3': return '3. Fremdsprache';
            case 'FOREIGN_LANGUAGE_4': return '4. Fremdsprache';
            case 'RELIGION': return 'Religion';
            case 'PROFILE': return 'Profil';
            case 'ORIENTATION': return 'Wahlbereich';
            case 'ELECTIVE': return 'Wahlfach';
            default: return '';
        }
    }

    /**
     * @return string
     */
    public function getSubjectAcronym(): string
    {
        if (($tblSubject = $this->getServiceTblSubject())) {
            return $tblSubject->getAcronym();
        }

        switch ($this->getStudentMetaIdentifier()) {
            case 'FOREIGN_LANGUAGE_1': return '1. FS';
            case 'FOREIGN_LANGUAGE_2': return '2. FS';
            case 'FOREIGN_LANGUAGE_3': return '3. FS';
            case 'FOREIGN_LANGUAGE_4': return '4. FS';
            case 'RELIGION': return 'R';
            case 'PROFILE': return 'P';
            case 'ORIENTATION': return 'Wahlbereich';
            case 'ELECTIVE': return 'Wahlfach';
            default: return '';
        }
    }

    /**
     * @return string
     */
    public function getStudentMetaDisplayName(): string
    {
        switch ($this->getStudentMetaIdentifier()) {
            case 'FOREIGN_LANGUAGE_1': return '1. Fremdsprache';
            case 'FOREIGN_LANGUAGE_2': return '2. Fremdsprache';
            case 'FOREIGN_LANGUAGE_3': return '3. Fremdsprache';
            case 'FOREIGN_LANGUAGE_4': return '4. Fremdsprache';
            case 'RELIGION': return 'Religion';
            case 'PROFILE': return 'Profil';
            case 'ORIENTATION': return 'Wahlbereich';
            case 'ELECTIVE': return 'Wahlfach';
            default: return '';
        }
    }

    public function getIsFixed(): bool
    {
        return ($this->getServiceTblSubject()
            && !$this->getStudentMetaIdentifier()
            && !(DivisionCourse::useService()->getSubjectTableLinkBySubjectTable($this)));
    }
}