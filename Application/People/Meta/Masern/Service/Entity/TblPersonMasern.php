<?php
namespace SPHERE\Application\People\Meta\Masern\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPersonMasern")
 * @Cache(usage="READ_ONLY")
 */
class TblPersonMasern extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_MASERN_DATE = 'MasernDate';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="datetime")
     */
    protected $MasernDate;
    /**
     * @Column(type="string")
     */
    protected $MasernDocumentType;
    /**
     * @Column(type="string")
     */
    protected $MasernCreatorType;

    /**
     * @return bool|TblPerson
     */
    public function getserviceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return false|string
     */
    public function getMasernDate()
    {

        if (null === $this->MasernDate) {
            return false;
        }
        /** @var DateTime MasernDate */
        $MasernDate = $this->MasernDate;
        if ($MasernDate instanceof DateTime) {
            return $MasernDate->format('d.m.Y');
        } else {
            return (string)$MasernDate;
        }
    }

    /**
     * @param null|DateTime $MasernDate
     */
    public function setMasernDate(DateTime $MasernDate = null)
    {

        $this->MasernDate = $MasernDate;
    }

    /**
     * @return TblStudentMasernInfo|false
     */
    public function getMasernDocumentType()
    {

        if (null === $this->MasernDocumentType) {
            return false;
        } else {
            return Student::useService()->getStudentMasernInfoById($this->MasernDocumentType);
        }
    }

    /**
     * @param TblStudentMasernInfo|null $MasernDocumentType
     */
    public function setMasernDocumentType(TblStudentMasernInfo $MasernDocumentType = null)
    {

        $this->MasernDocumentType = ( null === $MasernDocumentType ? null : $MasernDocumentType->getId() );
    }

    /**
     * @return TblStudentMasernInfo|false
     */
    public function getMasernCreatorType()
    {

        if (null === $this->MasernCreatorType) {
            return false;
        } else {
            return Student::useService()->getStudentMasernInfoById($this->MasernCreatorType);
        }
    }

    /**
     * @param TblStudentMasernInfo|null $MasernCreatorType
     */
    public function setMasernCreatorType(TblStudentMasernInfo $MasernCreatorType = null)
    {

        $this->MasernCreatorType = ( null === $MasernCreatorType ? null : $MasernCreatorType->getId() );
    }

}
