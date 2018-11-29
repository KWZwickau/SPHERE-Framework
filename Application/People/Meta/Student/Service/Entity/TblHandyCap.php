<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblHandyCap")
 * @Cache(usage="READ_ONLY")
 */
class TblHandyCap extends Element
{

    const LEGAL_BASES_SCHOOL_VIEW = 'Schulaufsicht';
    const LEGAL_BASES_INTERN = 'Schulintern';
    const LEARN_TARGET_EQUAL = 'lernzielgleich';
    const LEARN_TARGET_DIFFERENT = 'lernzieldifferneziert';

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_DATE = 'Date';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="datetime")
     */
    protected $Date;
    /**
     * @Column(type="bigint")
     */
    protected $PersonEditor;
    /**
     * @Column(type="string")
     */
    protected $LegalBasis;
    /**
     * @Column(type="string")
     */
    protected $LearnTarget;
    /**
     * @Column(type="string")
     */
    protected $RemarkLesson;
    /**
     * @Column(type="string")
     */
    protected $RemarkRating;
    /**
     * @Column(type="boolean")
     */
    protected $IsCanceled;


    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $SchoolAttendanceStartDate */
        $SchoolAttendanceStartDate = $this->Date;
        if ($SchoolAttendanceStartDate instanceof \DateTime) {
            return $SchoolAttendanceStartDate->format('d.m.Y');
        } else {
            return (string)$SchoolAttendanceStartDate;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @param string $SupportTime
     */
    public function setSupportTime($SupportTime)
    {

        $this->SupportTime = $SupportTime;
    }

    /**
     * @return false|TblPerson
     */
    public function getPersonEditor()
    {

        return $this->PersonEditor;
    }

    /**
     * @param string $PersonEditor
     */
    public function setPersonEditor($PersonEditor = null)
    {

        $this->PersonEditor = $PersonEditor;
    }

    /**
     * @return string
     */
    public function getLegalBasis()
    {
        return $this->LegalBasis;
    }

    /**
     * @param string $LegalBasis
     */
    public function setLegalBasis($LegalBasis = '')
    {
        $this->LegalBasis = $LegalBasis;
    }

    /**
     * @return string
     */
    public function getLearnTarget()
    {
        return $this->LearnTarget;
    }

    /**
     * @param string $LearnTarget
     */
    public function setLearnTarget($LearnTarget = '')
    {
        $this->LearnTarget = $LearnTarget;
    }

    /**
     * @param bool $WithHTML
     *
     * @return string
     */
    public function getRemarkLesson($WithHTML = true)
    {

        if($WithHTML){
            return nl2br($this->RemarkLesson);
        } else {
            return $this->RemarkLesson;
        }
    }

    /**
     * @param string $RemarkLesson
     */
    public function setRemarkLesson($RemarkLesson)
    {

        $this->RemarkLesson = $RemarkLesson;
    }

    /**
     * @param bool $WithHTML
     *
     * @return string
     */
    public function getRemarkRating($WithHTML = true)
    {

        if($WithHTML){
            return nl2br($this->RemarkRating);
        } else {
            return $this->RemarkRating;
        }
    }

    /**
     * @param string $RemarkRating
     */
    public function setRemarkRating($RemarkRating)
    {

        $this->RemarkRating = $RemarkRating;
    }

    /**
     * @return boolean
     */
    public function isCanceled()
    {
        return $this->IsCanceled;
    }

    /**
     * @param boolean $IsCanceled
     */
    public function setIsCanceled($IsCanceled)
    {
        $this->IsCanceled = $IsCanceled;
    }
}
