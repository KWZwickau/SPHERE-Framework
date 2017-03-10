<?php
namespace SPHERE\Application\Setting\User\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblToPersonMail;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblUserAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblUserAccount extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_TO_PERSON_ADDRESS = 'serviceTblToPersonAddress';
    const ATTR_SERVICE_TBL_TO_PERSON_MAIL = 'serviceTblToPersonMail';
    const ATTR_USER_NAME = 'userName';
    const ATTR_USER_PASS = 'userPass';
    const ATTR_IS_SEND = 'IsSend';
    const ATTR_IS_EXPORT = 'IsExport';


    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToPersonAddress;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToPersonMail;
    /**
     * @Column(type="string")
     */
    protected $userName;
    /**
     * @Column(type="string")
     */
    protected $userPass;
    /**
     * @Column(type="boolean")
     */
    protected $IsSend;
    /**
     * @Column(type="boolean")
     */
    protected $IsExport;

    /**
     * @return false|TblPerson
     */
    public function getServiceTblPerson()
    {

        $tblPerson = ( $this->serviceTblPerson != null ? Person::useService()->getPersonById($this->serviceTblPerson) : false );
        if ($tblPerson) {
            return $tblPerson;
        }
        return false;
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return false|TblToPersonAddress
     */
    public function getServiceTblToPersonAddress()
    {

        $tblToPersonAddress = ( $this->serviceTblToPersonAddress != null
            ? Address::useService()->getAddressToPersonById($this->serviceTblPerson)
            : false );
        if ($tblToPersonAddress) {
            return $tblToPersonAddress;
        }
        return false;
    }

    /**
     * @param null|TblToPersonAddress $tblToPersonAddress
     */
    public function setServiceTblToPersonAddress(TblToPersonAddress $tblToPersonAddress = null)
    {

        $this->serviceTblToPersonAddress = ( null === $tblToPersonAddress ? null : $tblToPersonAddress->getId() );
    }

    /**
     * @return false|TblToPersonMail
     */
    public function getServiceTblToPersonMail()
    {

        $tblToPersonMail = ( $this->serviceTblToPersonMail != null
            ? Mail::useService()->getMailToPersonById($this->serviceTblPerson)
            : false );
        if ($tblToPersonMail) {
            return $tblToPersonMail;
        }
        return false;
    }

    /**
     * @param null|TblToPersonMail $tblToPersonMail
     */
    public function setServiceTblToPersonMail(TblToPersonMail $tblToPersonMail = null)
    {

        $this->serviceTblToPersonMail = ( null === $tblToPersonMail ? null : $tblToPersonMail->getId() );
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName = '')
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserPass()
    {
        return $this->userPass;
    }

    /**
     * @param string $userPass
     */
    public function setUserPass($userPass = '')
    {
        $this->userPass = $userPass;
    }

    /**
     * @return string
     */
    public function getIsSend()
    {
        return $this->IsSend;
    }

    /**
     * @param string $IsSend
     */
    public function setIsSend($IsSend = '')
    {
        $this->IsSend = $IsSend;
    }

    /**
     * @return string
     */
    public function getIsExport()
    {
        return $this->IsExport;
    }

    /**
     * @param string $IsExport
     */
    public function setIsExport($IsExport = '')
    {
        $this->IsExport = $IsExport;
    }
}
