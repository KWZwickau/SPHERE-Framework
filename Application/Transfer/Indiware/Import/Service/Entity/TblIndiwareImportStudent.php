<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblIndiwareImportStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblIndiwareImportStudent extends Element
{

    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_LEVEL_STRING = 'Level';
    const ATTR_IS_IGNORE = 'IsIgnore';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected $Level;
    /**
     * @Column(type="boolean")
     */
    protected $IsIgnore;

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

        $this->serviceTblYear = (null === $tblYear ? null : $tblYear->getId());
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
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {

        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {

        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->Level;
    }

    /**
     * @param string $Level
     */
    public function setLevel($Level)
    {
        $this->Level = $Level;
    }

    /**
     * @return bool
     */
    public function getIsIgnore()
    {

        return $this->IsIgnore;
    }

    /**
     * @param bool $IsIgnore
     */
    public function setIsIgnore($IsIgnore)
    {

        $this->IsIgnore = $IsIgnore;
    }
}
