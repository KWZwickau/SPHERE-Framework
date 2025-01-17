<?php

namespace SPHERE\Application\Transfer\Education\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblImportStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblImportStudent extends Element
{
    const ATTR_TBL_IMPORT = 'tblImport';

    /**
     * @Column(type="bigint")
     */
    protected int $tblImport;
    /**
     * @Column(type="string")
     */
    protected string $FirstName;
    /**
     * @Column(type="string")
     */
    protected string $LastName;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $Birthday;
    /**
     * @Column(type="string")
     */
    protected string $GenderAcronym;
    /**
     * @Column(type="string")
     */
    protected string $DivisionName;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson = null;

    public function __construct(TblImport $tblImport, string $FirstName, string $LastName, ?DateTime $Birthday, string $GenderAcronym, string $DivisionName)
    {
        $this->tblImport = $tblImport->getId();
        $this->FirstName = $FirstName;
        $this->LastName = $LastName;
        $this->Birthday = $Birthday;
        $this->GenderAcronym = $GenderAcronym;
        $this->DivisionName = $DivisionName;
    }

    /**
     * @return TblImport|false
     */
    public function getTblImport()
    {
        return Education::useService()->getImportById($this->tblImport);
    }

    /**
     * @param TblImport $tblImport
     */
    public function setTblImport(TblImport $tblImport): void
    {
        $this->tblImport = $tblImport->getId();
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->FirstName;
    }

    /**
     * @param string $FirstName
     */
    public function setFirstName(string $FirstName): void
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->LastName;
    }

    /**
     * @param string $LastName
     */
    public function setLastName(string $LastName): void
    {
        $this->LastName = $LastName;
    }

    /**
     * @return DateTime|null
     */
    public function getBirthday(): ?DateTime
    {
        return $this->Birthday;
    }

    /**
     * @param DateTime|null $Birthday
     */
    public function setBirthday(?DateTime $Birthday): void
    {
        $this->Birthday = $Birthday;
    }

    /**
     * @return string
     */
    public function getGenderAcronym(): string
    {
        return $this->GenderAcronym;
    }

    /**
     * @param string $GenderAcronym
     */
    public function setGenderAcronym(string $GenderAcronym): void
    {
        $this->GenderAcronym = $GenderAcronym;
    }

    /**
     * @return string
     */
    public function getDivisionName(): string
    {
        return $this->DivisionName;
    }

    /**
     * @param string $DivisionName
     */
    public function setDivisionName(string $DivisionName): void
    {
        $this->DivisionName = $DivisionName;
    }

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
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return string
     */
    public function getLastFirstName(): string
    {
        return $this->getLastName() . ', ' . $this->getFirstName();
    }

    /**
     * SchülerBildung, der gefundene Person in der Schulsoftware oder gemappten Person
     *
     * @return bool|TblStudentEducation
     */
    public function getStudentEducation()
    {
        if (($tblImport = $this->getTblImport())
            && ($tblYear = $tblImport->getServiceTblYear())
        ) {
            if (!($tblPerson = $this->getServiceTblPerson())) {
                $tblPerson = Education::useService()->getPersonIsInCourseSystemByFristNameAndLastName(
                    $this->getFirstName(),
                    $this->getLastName(),
                    $tblYear,
                    $this->getBirthday() ?: null
                );
            }

            if ($tblPerson) {
                return DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
            }
        }

        return false;
    }
}