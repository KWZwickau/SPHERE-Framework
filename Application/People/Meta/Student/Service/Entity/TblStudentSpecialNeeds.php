<?php

namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentSpecialNeeds")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSpecialNeeds extends Element
{
    /**
     * @Column(type="boolean")
     */
    protected $IsMultipleHandicapped;
    /**
     * @Column(type="boolean")
     */
    protected $IsHeavyMultipleHandicapped;
    /**
     * @Column(type="string")
     */
    protected $IncreaseFactorHeavyMultipleHandicappedSchool;
    /**
     * @Column(type="string")
     */
    protected $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities;
    /**
     * @Column(type="string")
     */
    protected $RemarkHeavyMultipleHandicapped;
    /**
     * @Column(type="string")
     */
    protected $DegreeOfHandicap;
    /**
     * @Column(type="string")
     */
    protected $Sign;
    /**
     * @Column(type="datetime")
     */
    protected $ValidToDate;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentSpecialNeedsLevel;

    /**
     * @return boolean
     */
    public function getIsMultipleHandicapped()
    {
        return $this->IsMultipleHandicapped;
    }

    /**
     * @param boolean $IsMultipleHandicapped
     */
    public function setIsMultipleHandicapped($IsMultipleHandicapped)
    {
        $this->IsMultipleHandicapped = $IsMultipleHandicapped;
    }

    /**
     * @return boolean
     */
    public function getIsHeavyMultipleHandicapped()
    {
        return $this->IsHeavyMultipleHandicapped;
    }

    /**
     * @param boolean $IsHeavyMultipleHandicapped
     */
    public function setIsHeavyMultipleHandicapped($IsHeavyMultipleHandicapped)
    {
        $this->IsHeavyMultipleHandicapped = $IsHeavyMultipleHandicapped;
    }

    /**
     * @return string
     */
    public function getIncreaseFactorHeavyMultipleHandicappedSchool()
    {
        return $this->IncreaseFactorHeavyMultipleHandicappedSchool;
    }

    /**
     * @param string $IncreaseFactorHeavyMultipleHandicappedSchool
     */
    public function setIncreaseFactorHeavyMultipleHandicappedSchool($IncreaseFactorHeavyMultipleHandicappedSchool)
    {
        $this->IncreaseFactorHeavyMultipleHandicappedSchool = $IncreaseFactorHeavyMultipleHandicappedSchool;
    }

    /**
     * @return string
     */
    public function getIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities()
    {
        return $this->IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities;
    }

    /**
     * @param string $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities
     */
    public function setIncreaseFactorHeavyMultipleHandicappedRegionalAuthorities(
        $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities
    ) {
        $this->IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities = $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities;
    }

    /**
     * @return string
     */
    public function getRemarkHeavyMultipleHandicapped()
    {
        return $this->RemarkHeavyMultipleHandicapped;
    }

    /**
     * @param string $RemarkHeavyMultipleHandicapped
     */
    public function setRemarkHeavyMultipleHandicapped($RemarkHeavyMultipleHandicapped)
    {
        $this->RemarkHeavyMultipleHandicapped = $RemarkHeavyMultipleHandicapped;
    }

    /**
     * @return string
     */
    public function getDegreeOfHandicap()
    {
        return $this->DegreeOfHandicap;
    }

    /**
     * @param string $DegreeOfHandicap
     */
    public function setDegreeOfHandicap($DegreeOfHandicap)
    {
        $this->DegreeOfHandicap = $DegreeOfHandicap;
    }

    /**
     * @return string|false
     */
    public function getValidToDate()
    {

        if (null === $this->ValidToDate) {
            return false;
        }
        /** @var DateTime $ValidToDate */
        $ValidToDate = $this->ValidToDate;
        if ($ValidToDate instanceof DateTime) {
            return $ValidToDate->format('d.m.Y');
        } else {
            return (string)$ValidToDate;
        }
    }

    /**
     * @param null|DateTime $ValidToDate
     */
    public function setValidToDate(DateTime $ValidToDate = null)
    {

        $this->ValidToDate = $ValidToDate;
    }

    /**
     * @return bool|TblStudentSpecialNeedsLevel
     */
    public function getTblStudentSpecialNeedsLevel()
    {

        if (null === $this->tblStudentSpecialNeedsLevel) {
            return false;
        } else {
            return Student::useService()->getStudentSpecialNeedsLevelById($this->tblStudentSpecialNeedsLevel);
        }
    }

    /**
     * @param null|TblStudentSpecialNeedsLevel $tblStudentSpecialNeedsLevel
     */
    public function setTblStudentSpecialNeedsLevel(TblStudentSpecialNeedsLevel $tblStudentSpecialNeedsLevel = null)
    {

        $this->tblStudentSpecialNeedsLevel = ( null === $tblStudentSpecialNeedsLevel ? null : $tblStudentSpecialNeedsLevel->getId() );
    }
}