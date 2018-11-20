<?php
namespace SPHERE\Application\Billing\Accounting\Creditor\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCreditor")
 * @Cache(usage="READ_ONLY")
 */
class TblCreditor extends Element
{
//    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';
//    const ATTR_SERVICE_TBL_TYPE = 'serviceTblType';
    const ATTR_OWNER = 'Owner';
    const ATTR_STREET = 'Street';
    const ATTR_NUMBER = 'Number';
    const ATTR_CODE = 'Code';
    const ATTR_CITY = 'City';
    const ATTR_DISTRICT = 'District';
    const ATTR_CREDITOR_ID = 'CreditorId';
    const ATTR_BANK_NAME = 'BankName';
    const ATTR_IBAN = 'IBAN';
    const ATTR_BIC = 'BIC';

    /**
     * @Column(type="string")
     */
    protected $Owner;
    /**
     * @Column(type="string")
     */
    protected $Street;
    /**
     * @Column(type="string")
     */
    protected $Number;
    /**
     * @Column(type="string")
     */
    protected $Code;
    /**
     * @Column(type="string")
     */
    protected $City;
    /**
     * @Column(type="string")
     */
    protected $District;
    /**
     * @Column(type="string")
     */
    protected $CreditorId;
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
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceTblCompany;
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceTblType;

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

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->Street;
    }

    /**
     * @param string $Street
     */
    public function setStreet($Street)
    {
        $this->Street = $Street;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->Number;
    }

    /**
     * @param string $Number
     */
    public function setNumber($Number)
    {
        $this->Number = $Number;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->Code;
    }

    /**
     * @param string $Code
     */
    public function setCode($Code)
    {
        $this->Code = $Code;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->City;
    }

    /**
     * @param string $City
     */
    public function setCity($City)
    {
        $this->City = $City;
    }

    /**
     * @return mixed
     */
    public function getDistrict()
    {
        return $this->District;
    }

    /**
     * @param mixed $District
     */
    public function setDistrict($District)
    {
        $this->District = $District;
    }

    /**
     * @return string
     */
    public function getCreditorId()
    {
        return $this->CreditorId;
    }

    /**
     * @param string $CreditorId
     */
    public function setCreditorId($CreditorId)
    {
        $this->CreditorId = $CreditorId;
    }

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
     * @param bool $IsFormat
     *
     * @return string $IBAN
     */
    public function getIBAN($IsFormat = true)
    {

        if($IsFormat){
            $countLetter = strlen($this->IBAN);
            $IBANParts = array();
            for($i = 0; $i < $countLetter; $i+=4){
                $IBANParts[] = substr($this->IBAN, $i, 4);
            }
            return implode(' ', $IBANParts);
        }
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

//    /**
//     * @return bool|TblCompany
//     */
//    public function getServiceTblCompany()
//    {
//
//        if (null === $this->serviceTblCompany) {
//            return false;
//        } else {
//            return Company::useService()->getCompanyById($this->serviceTblCompany);
//        }
//    }
//
//    /**
//     * @param TblCompany|null $tblCompany
//     */
//    public function setServiceTblCompany(TblCompany $tblCompany = null)
//    {
//
//        $this->serviceTblCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
//    }

//    /**
//     * @return bool|TblType
//     */
//    public function getServiceTblType()
//    {
//
//        if (null === $this->serviceTblType) {
//            return false;
//        } else {
//            return Type::useService()->getTypeById($this->serviceTblType);
//        }
//    }
//
//    /**
//     * @param TblType|null $tblType
//     */
//    public function setServiceTblType(TblType $tblType = null)
//    {
//
//        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
//    }
}
