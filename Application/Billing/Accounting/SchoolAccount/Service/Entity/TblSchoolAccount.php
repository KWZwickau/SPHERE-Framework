<?php
namespace SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSchoolAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblSchoolAccount extends Element
{
//    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';
//    const ATTR_SERVICE_TBL_SCHOOL = 'serviceTblSchool';

    /**
     * @Column(type="string")
     */
    protected $BankName;
    /**
     * @Column(type="string")
     */
    protected $IBAN;
    /**
     * @Column(type="string")
     */
    protected $BIC;
    /**
     * @Column(type="string")
     */
    protected $Owner;
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceTblSchool;


    /**
     * @return string $BankName
     */
    public function getBankName()
    {

        return $this->BankName;
    }

    /**
     * @param string $BankName
     */
    public function setBankName($BankName)
    {

        $this->BankName = $BankName;
    }

    /**
     * @return string $IBAN
     */
    public function getIBAN()
    {

        return $this->IBAN;
    }

    /**
     * @param string $IBAN
     */
    public function setIBAN($IBAN)
    {

        $this->IBAN = strtoupper(substr(str_replace(' ', '', $IBAN), 0, 34));
    }

    /**
     * @return string $BIC
     */
    public function getBIC()
    {

        return $this->BIC;
    }

    /**
     * @param string $BIC
     */
    public function setBIC($BIC)
    {

        $this->BIC = strtoupper(substr(str_replace(' ', '', $BIC), 0, 11));
    }

    /**
     * @return string $Owner
     */
    public function getOwner()
    {

        return $this->Owner;
    }

    /**
     * @param string $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }

//    /**
//     * @return bool|TblPerson
//     */
//    public function getServiceTblPerson()
//    {
//
//        if (null === $this->serviceTblPerson) {
//            return false;
//        } else {
//            return Person::useService()->getPersonById($this->serviceTblPerson);
//        }
//    }
//
//    /**
//     * @param TblPerson|null $tblPerson
//     */
//    public function setServiceTblPerson(TblPerson $tblPerson = null)
//    {
//
//        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
//    }
//
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

//    /**
//     * @return bool|TblSchool
//     */
//    public function getServiceTblSchool()
//    {
//
//        if (null === $this->serviceTblSchool) {
//            return false;
//        } else {
//            return School::useService()->getSchoolById($this->serviceTblSchool);
//        }
//    }
//
//    /**
//     * @param TblSchool|null $tblSchool
//     */
//    public function setServiceTblSchool(TblSchool $tblSchool = null)
//    {
//
//        $this->serviceTblSchool = ( null === $tblSchool ? null : $tblSchool->getId() );
//    }
}
