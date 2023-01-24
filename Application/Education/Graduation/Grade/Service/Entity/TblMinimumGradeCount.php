<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Sorter;

/**
 * @Entity()
 * @Table(name="tblGraduationMinimumGradeCount")
 * @Cache(usage="READ_ONLY")
 */
class TblMinimumGradeCount extends Element
{
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';
    const ATTR_PERIOD = 'Period';
    const ATTR_HIGHLIGHTED = 'Highlighted';
    const ATTR_COURSE = 'Course';
    const ATTR_COUNT = 'Count';

    /**
     * @Column(type="integer")
     */
    protected int $Count;
    /**
     * @Column(type="bigint")
     */
    protected ?int $tblGraduationGradeType = null;
    /**
     * @Column(type="integer")
     */
    protected int $Period;
    /**
     * @Column(type="integer")
     */
    protected int $Highlighted;
    /**
     * @Column(type="integer")
     */
    protected int $Course;

    /**
     * @param int $Count
     * @param TblGradeType|null $tblGradeType
     * @param int $Period
     * @param int $Highlighted
     * @param int $Course
     */
    public function __construct(int $Count, ?TblGradeType $tblGradeType, int $Period, int $Highlighted, int $Course)
    {
        $this->Count = $Count;
        $this->tblGraduationGradeType = $tblGradeType ? $tblGradeType->getId() : null;
        $this->Period = $Period;
        $this->Highlighted = $Highlighted;
        $this->Course = $Course;
    }

    /**
     * @return integer
     */
    public function getCount(): int
    {
        return $this->Count;
    }

    /**
     * @param integer $Count
     */
    public function setCount(int $Count)
    {
        $this->Count = $Count;
    }

    /**
     * @return false|TblGradeType
     */
    public function getTblGradeType()
    {
        return $this->tblGraduationGradeType === null ? false : Grade::useService()->getGradeTypeById($this->tblGraduationGradeType);
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setTblGradeType(?TblGradeType $tblGradeType)
    {
        $this->tblGraduationGradeType = $tblGradeType ? $tblGradeType->getId() : null;
    }
    /**
     * @return string
     */
    public function getGradeTypeDisplayName(): string
    {
        if (($tblGradeType = $this->getTblGradeType())){
            return $tblGradeType->getDisplayName();
        } else {
            switch ($this->getHighlighted())  {
                case SelectBoxItem::HIGHLIGHTED_ALL: $gradeType = 'Alle Zensuren-Typen'; break;
                case SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED: $gradeType = 'Nur große Zensuren-Typen (Fett markiert)'; break;
                case SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED: $gradeType = 'Nur kleine Zensuren-Typen (nicht Fett markiert)'; break;
                default: $gradeType = '';
            }

            return $gradeType;
        }
    }

    /**
     * @return string
     */
    public function getGradeTypeDisplayShortName(): string
    {
        if (($tblGradeType = $this->getTblGradeType())){
            return $tblGradeType->getCode();
        } else {
            switch ($this->getHighlighted())  {
                case SelectBoxItem::HIGHLIGHTED_ALL: $gradeType = 'Alle Z-T'; break;
                case SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED: $gradeType = 'Große Z-T'; break;
                case SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED: $gradeType = 'Kleine Z-T'; break;
                default: $gradeType = '';
            }

            return $gradeType;
        }
    }

    /**
     * @return integer
     */
    public function getPeriod()
    {
        return $this->Period;
    }

    /**
     * @param integer $Period
     */
    public function setPeriod($Period)
    {
        $this->Period = $Period;
    }

    /**
     * @return integer
     */
    public function getHighlighted(): int
    {
        return $this->Highlighted;
    }

    /**
     * @param integer $Highlighted
     */
    public function setHighlighted(int $Highlighted)
    {
        $this->Highlighted = $Highlighted;
    }

    /**
     * @return integer
     */
    public function getCourse(): int
    {
        return $this->Course;
    }

    /**
     * @param integer $Course
     */
    public function setCourse(int $Course)
    {
        $this->Course = $Course;
    }

    /**
     * @return string
     */
    public function getPeriodDisplayName(): string
    {
        switch ($this->getPeriod())  {
            case SelectBoxItem::PERIOD_FULL_YEAR: $period = 'Gesamtes Schuljahr'; break;
            case SelectBoxItem::PERIOD_FIRST_PERIOD: $period = '1. Halbjahr'; break;
            case SelectBoxItem::PERIOD_SECOND_PERIOD: $period = '2. Halbjahr'; break;
            default: $period = '';
        }

        return $period;
    }

    /**
     * @return string
     */
    public function getCourseDisplayName(): string
    {
        switch ($this->getCourse())  {
            case SelectBoxItem::COURSE_NONE: $period = ''; break;
            case SelectBoxItem::COURSE_ADVANCED: $period = 'Leistungskurs'; break;
            case SelectBoxItem::COURSE_BASIC: $period = 'Grundkurs'; break;
            default: $period = '';
        }

        return $period;
    }

    /**
     * @return string
     */
    public function getLevelListDisplayName(): string
    {
        $result = '';
        if (($tempList = Grade::useService()->getMinimumGradeCountLevelLinkByMinimumGradeCount($this))) {
            $tempList = $this->getSorter($tempList)->sortObjectBy('DisplayName', new Sorter\StringNaturalOrderSorter());
            foreach ($tempList as $item) {
                $result .= ($result ? ', ' : '') . $item->getDisplayName();
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getSubjectListDisplayName(): string
    {
        $result = '';
        if (($tempList = Grade::useService()->getMinimumGradeCountSubjectLinkByMinimumGradeCount($this))) {
            $tempList = $this->getSorter($tempList)->sortObjectBy('DisplayName', new Sorter\StringNaturalOrderSorter());
            foreach ($tempList as $item) {
                $result .= ($result ? ', ' : '') . $item->getDisplayName();
            }
        }

        return $result;
    }
}