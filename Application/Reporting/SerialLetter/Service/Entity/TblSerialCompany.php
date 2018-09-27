<?php

namespace SPHERE\Application\Reporting\SerialLetter\Service\Entity;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter;
use SPHERE\System\Database\Fitting\Element;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity()
 * @Table(name="tblSerialCompany")
 * @Cache(usage="READ_ONLY")
 */
class TblSerialCompany extends Element
{

    const ATTR_TBL_SERIAL_LETTER = 'tblSerialLetter';
    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_IS_IGNORE = 'isIgnore';


    /**
     * @Column(type="bigint")
     */
    protected $tblSerialLetter;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="boolean")
     */
    protected $isIgnore;


    /**
     * @return bool|TblSerialLetter
     */
    public function getTblSerialLetter()
    {

        if (null === $this->tblSerialLetter) {
            return false;
        } else {
            return SerialLetter::useService()->getSerialLetterById($this->tblSerialLetter);
        }
    }

    /**
     * @param null|TblSerialLetter $tblSerialLetter
     */
    public function setTblSerialLetter(TblSerialLetter $tblSerialLetter = null)
    {

        $this->tblSerialLetter = ( null === $tblSerialLetter ? null : $tblSerialLetter->getId() );
    }

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblCompany()
    {

        if (null === $this->serviceTblCompany) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompany);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompany(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
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

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool
     */
    public function getisIgnore()
    {
        return $this->isIgnore;
    }

    /**
     * @param bool $isIgnore
     */
    public function setIsIgnore($isIgnore)
    {
        $this->isIgnore = $isIgnore;
    }
}