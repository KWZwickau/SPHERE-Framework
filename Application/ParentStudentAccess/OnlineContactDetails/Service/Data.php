<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service;

use SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service\Entity\TblOnlineContact;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param string $ContactType
     * @param Element|null $tblToPerson
     * @param Element $tblContact
     * @param TblPerson $tblPerson
     * @param string $Remark
     * @param TblPerson $tblPersonCreator
     * @param Element|null $tblNewContactType
     * @param bool $isEmergencyContact
     *
     * @return TblOnlineContact
     */
    public function createOnlineContact(
        string $ContactType,
        ?Element $tblToPerson,
        Element $tblContact,
        TblPerson $tblPerson,
        string $Remark,
        TblPerson $tblPersonCreator,
        Element $tblNewContactType  = null,
        bool $isEmergencyContact = false
    ): TblOnlineContact {
        $Manager = $this->getEntityManager();

        $Entity = new TblOnlineContact();
        $Entity->setContactType($ContactType);
        $Entity->setServiceTblToPerson($tblToPerson);
        $Entity->setServiceTblContact($tblContact);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setRemark($Remark);
        $Entity->setServiceTblPersonCreator($tblPersonCreator);
        $Entity->setServiceTblNewContactType($tblNewContactType);
        $Entity->setIsEmergencyContact($isEmergencyContact);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblOnlineContact $tblOnlineContact
     *
     * @return bool
     */
    public function deleteOnlineContact(TblOnlineContact $tblOnlineContact): bool
    {
        $Manager = $this->getEntityManager();

        /** @var TblOnlineContact $Entity */
        $Entity = $Manager->getEntityById('TblOnlineContact', $tblOnlineContact->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }
        return false;
    }

    /**
     * @param string $ContactType
     * @param Element $tblToPerson
     *
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAllByToPerson(string $ContactType, Element $tblToPerson)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblOnlineContact', array(
            TblOnlineContact::ATTR_CONTACT_TYPE => $ContactType,
            TblOnlineContact::ATTR_SERVICE_TBL_TO_PERSON => $tblToPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param string|null $ContactType
     *
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAllByPerson(TblPerson $tblPerson, ?string $ContactType = null)
    {
        if ($ContactType) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblOnlineContact', array(
                TblOnlineContact::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblOnlineContact::ATTR_CONTACT_TYPE => $ContactType
            ));
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblOnlineContact', array(
            TblOnlineContact::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param $Id
     *
     * @return false|TblOnlineContact
     */
    public function getOnlineContactById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblOnlineContact', $Id);
    }

    /**
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblOnlineContact');
    }

    /**
     * @param TblOnlineContact $tblOnlineContact
     *
     * @return false|TblOnlineContact[]
     */
    public function getOnlineContactAllByOnlineContact(TblOnlineContact $tblOnlineContact)
    {
        if (($tblContact = $tblOnlineContact->getServiceTblContact())) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblOnlineContact', array(
                TblOnlineContact::ATTR_CONTACT_TYPE => $tblOnlineContact->getContactType(),
                TblOnlineContact::ATTR_SERVICE_TBL_CONTACT => $tblContact->getId()
            ));
        }

        return false;
    }

    /**
     * @param Element $tblContact
     * @param string $contactType
     * @param TblPerson $tblPerson
     *
     * @return false|TblOnlineContact
     */
    public function getOnlineContactByContactAndPerson(Element $tblContact, string $contactType, TblPerson $tblPerson)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblOnlineContact', array(
            TblOnlineContact::ATTR_CONTACT_TYPE => $contactType,
            TblOnlineContact::ATTR_SERVICE_TBL_CONTACT => $tblContact->getId(),
            TblOnlineContact::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }
}