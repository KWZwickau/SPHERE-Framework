<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblOnlineContact")
 * @Cache(usage="READ_ONLY")
 */
class TblOnlineContact extends Element
{
    const ATTR_CONTACT_TYPE = 'ContactType';
    const ATTR_SERVICE_TBL_TO_PERSON = 'serviceTblToPerson';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    const VALUE_TYPE_ADDRESS = 'ADDRESS';
    const VALUE_TYPE_PHONE = 'PHONE';
    const VALUE_TYPE_MAIL = 'MAIL';

    /**
     * @Column(type="string")
     */
    protected string $ContactType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblToPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblContact;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="text")
     */
    protected string $Remark;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonCreator;

    /**
     * @return string
     */
    public function getContactType(): string
    {
        return $this->ContactType;
    }

    /**
     * @param string $ContactType
     */
    public function setContactType(string $ContactType): void
    {
        $this->ContactType = $ContactType;
    }

    /**
     * @return false|Element
     */
    public function getServiceTblToPerson()
    {
        if ($this->serviceTblToPerson) {
            switch ($this->getContactType()) {
                case self::VALUE_TYPE_ADDRESS: return Address::useService()->getAddressToPersonById($this->serviceTblToPerson);
                case self::VALUE_TYPE_PHONE: return Phone::useService()->getPhoneToPersonById($this->serviceTblToPerson);
                case self::VALUE_TYPE_MAIL: return Mail::useService()->getMailToPersonById($this->serviceTblToPerson);
            }
        }

        return false;
    }

    /**
     * @param Element|null $tblToPerson
     */
    public function setServiceTblToPerson(?Element $tblToPerson): void
    {
        $this->serviceTblToPerson = $tblToPerson ? $tblToPerson->getId() : null;
    }

    /**
     * @return false|Element
     */
    public function getServiceTblContact()
    {
        if ($this->serviceTblContact) {
            switch ($this->getContactType()) {
                case self::VALUE_TYPE_ADDRESS: return Address::useService()->getAddressById($this->serviceTblContact);
                case self::VALUE_TYPE_PHONE: return Phone::useService()->getPhoneById($this->serviceTblContact);
                case self::VALUE_TYPE_MAIL: return Mail::useService()->getMailById($this->serviceTblContact);
            }
        }

        return false;
    }

    /**
     * @param Element $tblContact
     */
    public function setServiceTblContact(Element $tblContact): void
    {
        $this->serviceTblContact = $tblContact->getId();
    }

    /**
     * @return false|TblPerson
     */
    public function getServiceTblPerson()
    {
        return Person::useService()->getPersonById($this->serviceTblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson): void
    {
        $this->serviceTblPerson = $tblPerson->getId();
    }

    /**
     * @return string
     */
    public function getRemark(): string
    {
        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark(string $Remark): void
    {
        $this->Remark = $Remark;
    }

    /**
     * @return false|TblPerson
     */
    public function getServiceTblPersonCreator()
    {
        return Person::useService()->getPersonById($this->serviceTblPersonCreator);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPersonCreator(TblPerson $tblPerson): void
    {
        $this->serviceTblPersonCreator = $tblPerson->getId();
    }

    /**
     * @return string
     */
    public function getContactString(): string
    {
        if (($tblContact = $this->getServiceTblContact())) {
            $creator = ($tblPerson = $this->getServiceTblPersonCreator())
                ? new PullRight(new Muted(new Small(' (' . $tblPerson->getFullName() . ' am ' . $this->EntityCreate->format('d.m.Y') . ')')))
                : '';

            switch ($this->getContactType()) {
                case self::VALUE_TYPE_ADDRESS:
                    /** @var TblAddress $tblContact */
                    return $tblContact->getGuiString() . $creator;
                case self::VALUE_TYPE_PHONE:
                    /** @var TblPhone $tblContact */
                    return $tblContact->getNumber() . $creator;
                case self::VALUE_TYPE_MAIL:
                    /** @var TblMail $tblContact */
                    return $tblContact->getAddress() . $creator;
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getContactTypeName(): string
    {
        switch ($this->getContactType()) {
            case self::VALUE_TYPE_ADDRESS: return 'Adresse';
            case self::VALUE_TYPE_PHONE: return 'Telefonnummer';
            case self::VALUE_TYPE_MAIL: return 'E-Mail-Adresse';
        }

        return '';
    }
}