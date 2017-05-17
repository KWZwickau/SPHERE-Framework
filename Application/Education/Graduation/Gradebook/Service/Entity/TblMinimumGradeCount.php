<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 20.09.2016
 * Time: 08:19
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblMinimumGradeCount")
 * @Cache(usage="READ_ONLY")
 */
class TblMinimumGradeCount extends Element
{

    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_LEVEL = 'serviceTblLevel';
    const ATTR_PERIOD = 'Period';
    const ATTR_HIGHLIGHTED = 'Highlighted';

    /**
     * @Column(type="integer")
     */
    protected $Count;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblLevel;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="integer")
     */
    protected $Period;

    /**
     * @Column(type="integer")
     */
    protected $Highlighted;

    /**
     * @return bool|TblGradeType
     */
    public function getTblGradeType()
    {

        if (null === $this->tblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->tblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setTblGradeType($tblGradeType)
    {

        $this->tblGradeType = ( null === $tblGradeType ? null : $tblGradeType->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblLevel
     */
    public function getServiceTblLevel()
    {

        if (null === $this->serviceTblLevel) {
            return false;
        } else {
            return Division::useService()->getLevelById($this->serviceTblLevel);
        }
    }

    /**
     * @param null|TblLevel $serviceTblLevel
     */
    public function setServiceTblLevel(TblLevel $serviceTblLevel = null)
    {

        $this->serviceTblLevel = ( null === $serviceTblLevel ? null : $serviceTblLevel->getId() );
    }

    /**
     * @return integer
     */
    public function getCount()
    {
        return $this->Count;
    }

    /**
     * @param integer $Count
     */
    public function setCount($Count)
    {
        $this->Count = $Count;
    }

    /**
     * @return string
     */
    public function getSchoolTypeDisplayName()
    {
        if ($this->getServiceTblLevel() && ($tblSchoolType = $this->getServiceTblLevel()->getServiceTblType())) {
            return $tblSchoolType->getName();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getLevelDisplayName()
    {
        if ($this->getServiceTblLevel()){
            return $this->getServiceTblLevel()->getName();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getSubjectDisplayName()
    {
        if (($tblSubject = $this->getServiceTblSubject())){
            return $tblSubject ? $tblSubject->getAcronym() . ' - ' . $tblSubject->getName() : '';
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getGradeTypeDisplayName()
    {
        if (($tblGradeType = $this->getTblGradeType())){
            return $tblGradeType ? $tblGradeType->getCode() . ' - ' . $tblGradeType->getName() : '';
        } else {
            switch ($this->getHighlighted())  {
                case SelectBoxItem::HIGHLIGHTED_ALL: $gradeType = 'Alle Zensuren-Typen'; break;
                case SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED: $gradeType = 'Nur große Zensuren-Typen (Fett marktiert)'; break;
                case SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED: $gradeType = 'Nur kleine Zensuren-Typen (nicht Fett markiert)'; break;
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
     * @return mixed
     */
    public function getHighlighted()
    {
        return $this->Highlighted;
    }

    /**
     * @param mixed $Highlighted
     */
    public function setHighlighted($Highlighted)
    {
        $this->Highlighted = $Highlighted;
    }

    /**
     * @return string
     */
    public function getPeriodDisplayName()
    {

        switch ($this->getPeriod())  {
            case SelectBoxItem::PERIOD_FULL_YEAR: $period = 'Gesamtes Schuljahr'; break;
            case SelectBoxItem::PERIOD_FIRST_PERIOD: $period = '1. Zeitraum'; break;
            case SelectBoxItem::PERIOD_SECOND_PERIOD: $period = '2. Zeitraum'; break;
            default: $period = '';
        }

        return $period;
    }
}