<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblAddressToPerson;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblMailToPerson;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson as TblPhoneToPerson;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Mail as MailIcon;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Phone as PhoneIcon;
use SPHERE\Common\Frontend\Text\Repository\Muted;
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
    const ATTR_SERVICE_TBL_CONTACT = 'serviceTblContact';

    const VALUE_TYPE_ADDRESS = 'ADDRESS';
    const VALUE_TYPE_PHONE = 'PHONE';
    const VALUE_TYPE_MAIL = 'MAIL';

    /**
     * ADDRESS, PHONE or MAIL
     *
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
     * Typ bei der Telefonnummer oder Email-Adresse
     *
     * @Column(type="bigint")
     */
    protected $serviceTblNewContactType = null;

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
     * @Column(type="boolean")
     */
    protected $IsEmergencyContact;

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

    public function getContactCreate(): string
    {
        return ($tblPerson = $this->getServiceTblPersonCreator())
            ? new Muted($tblPerson->getFullName() . ' am ' . $this->EntityCreate->format('d.m.Y'))
            : '';
    }

    /**
     * @return string
     */
    public function getContactContent(): string
    {
        if (($tblContact = $this->getServiceTblContact())) {
            switch ($this->getContactType()) {
                case self::VALUE_TYPE_ADDRESS:
                    /** @var TblAddress $tblContact */
                    return $tblContact->getGuiTwoRowString();
                case self::VALUE_TYPE_PHONE:
                    /** @var TblPhone $tblContact */
                    return $tblContact->getNumber();
                case self::VALUE_TYPE_MAIL:
                    /** @var TblMail $tblContact */
                    return $tblContact->getAddress();
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getOriginalContent(): string
    {
        if (($tblToPerson = $this->getServiceTblToPerson())) {
            switch ($this->getContactType()) {
                case self::VALUE_TYPE_ADDRESS:
                    /** @var TblAddressToPerson $tblToPerson */
                    return $tblToPerson->getTblAddress()->getGuiTwoRowString();
                case self::VALUE_TYPE_PHONE:
                    /** @var TblPhoneToPerson $tblToPerson */
                    return $tblToPerson->getTblPhone()->getNumber();
                case self::VALUE_TYPE_MAIL:
                    /** @var TblMailToPerson $tblToPerson */
                    return $tblToPerson->getTblMail()->getAddress();
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getContactTypeName(): string
    {
        if (!$this->getContactType()) {
            return '';
        }

        switch ($this->getContactType()) {
            case self::VALUE_TYPE_ADDRESS: $result =  'Adresse'; break;
            case self::VALUE_TYPE_PHONE: $result = 'Telefonnummer'; break;
            case self::VALUE_TYPE_MAIL: $result = 'E-Mail-Adresse'; break;
            default: $result =  '';

        }

        if ($this->getServiceTblToPerson()) {
            return $result . ' (Änderungswunsch)';
        } else {
            return ' Neue ' . $result;
        }
    }

    /**
     * @return string
     */
    public function getContactTypeIcon(): string
    {
        switch ($this->getContactType()) {
            case self::VALUE_TYPE_ADDRESS: return new MapMarker();
            case self::VALUE_TYPE_PHONE: return new PhoneIcon();
            case self::VALUE_TYPE_MAIL: return new MailIcon();
        }

        return '';
    }

    /**
     * @return Element|false
     */
    public function getServiceTblNewContactType()
    {
        if ($this->serviceTblNewContactType) {
            switch ($this->getContactType()) {
                case self::VALUE_TYPE_PHONE: return Phone::useService()->getTypeById($this->serviceTblNewContactType);
                case self::VALUE_TYPE_MAIL: return Mail::useService()->getTypeById($this->serviceTblNewContactType);
            }
        }

        return false;
    }

    /**
     * @param mixed $tblNewContactType
     */
    public function setServiceTblNewContactType(Element $tblNewContactType = null): void
    {
        if ($tblNewContactType) {
            $this->serviceTblNewContactType = $tblNewContactType->getId();
        } else {
            $this->serviceTblNewContactType = null;
        }
    }

    /**
     * @return bool
     */
    public function getIsEmergencyContact(): bool
    {
        return $this->IsEmergencyContact;
    }

    /**
     * @param bool $IsEmergencyContact
     */
    public function setIsEmergencyContact(bool $IsEmergencyContact): void
    {
        $this->IsEmergencyContact = $IsEmergencyContact;
    }
}