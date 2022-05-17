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
     *
     * @return TblOnlineContact
     */
    public function createOnlineContact(
        string $ContactType,
        ?Element $tblToPerson,
        Element $tblContact,
        TblPerson $tblPerson,
        string $Remark,
        TblPerson $tblPersonCreator
    ): TblOnlineContact {
        $Manager = $this->getEntityManager();

        $Entity = new TblOnlineContact();
        $Entity->setContactType($ContactType);
        $Entity->setServiceTblToPerson($tblToPerson);
        $Entity->setServiceTblContact($tblContact);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setRemark($Remark);
        $Entity->setServiceTblPersonCreator($tblPersonCreator);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
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
}