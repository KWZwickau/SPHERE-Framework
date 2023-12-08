<?php
namespace SPHERE\Application\Contact\Phone\Service;

use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\Contact\Phone\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createType('Privat', 'Festnetz');
        $this->createType('Privat', 'Mobil');
        $this->createType('Geschäftlich', 'Festnetz');
        $this->createType('Geschäftlich', 'Mobil');
        $this->createType('Fax', 'Privat');
        $this->createType('Fax', 'Geschäftlich');

        // todo kann nach der Migration (DB-Update) gelöscht werden

        $deleteBulkList = array();
        // Telefonnummern finden, welche bei einer Person mehrmals gespeichert sind und mindestens eine als Notfallnummer
        if ($this->getTypeByNameAndDescription('Notfall', 'Festnetz')) {
            if (($duplicateList = $this->getPhoneDuplicate())) {
                foreach ($duplicateList as $item) {
                    if (isset($item['serviceTblPerson'])
                        && ($tblPerson = Person::useService()->getPersonById($item['serviceTblPerson']))
                        && ($tblTempList = $this->getPhoneAllByPerson($tblPerson))
                    ) {
                        $list = array();
                        foreach ($tblTempList as $tblTemp) {
                            $list[$tblTemp->getTblPhone()->getId()]['List'][$tblTemp->getId()] = $tblTemp;
                            if ($tblTemp->getTblType()->getName() == 'Notfall') {
                                $list[$tblTemp->getTblPhone()->getId()]['hasEmergencyContact'] = true;
                            }
                        }

                        foreach ($list as $array) {
                            if (isset($array['hasEmergencyContact'])) {
                                /** @var TblToPerson $tblToPersonTemp */
                                foreach ($array['List'] as $tblToPersonTemp) {
                                    if ($tblToPersonTemp->getTblType()->getName() != 'Notfall') {
                                        $deleteBulkList[] = $tblToPersonTemp;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $updateBulkList = array();
        if (($tblType = $this->getTypeByNameAndDescription('Notfall', 'Festnetz'))) {
            // Migration Notfall-Telefonnummern
            if (($tblPhoneToPersonList = $this->getPhoneToPersonListByType($tblType))) {
                $tblTypeNew = $this->getTypeByNameAndDescription('Privat', 'Festnetz');
                foreach ($tblPhoneToPersonList as $tblToPerson) {
                    $tblToPerson->setIsEmergencyContact(true);
                    $tblToPerson->setTblType($tblTypeNew);

                    $updateBulkList[] = $tblToPerson;
                }
            }
            if (($tblPhoneToCompanyList = $this->getPhoneToCompanyListByType($tblType))) {
                $tblTypeNew = $this->getTypeByNameAndDescription('Privat', 'Festnetz');
                foreach ($tblPhoneToCompanyList as $tblToCompany) {
                    $tblToCompany->setIsEmergencyContact(true);
                    $tblToCompany->setTblType($tblTypeNew);

                    $updateBulkList[] = $tblToCompany;
                }
            }

            // Typ soft löschen
            $this->removeType($tblType);
        }
        if (($tblType = $this->getTypeByNameAndDescription('Notfall', 'Mobil'))) {
            // Migration Notfall-Telefonnummern
            if (($tblPhoneToPersonList = $this->getPhoneToPersonListByType($tblType))) {
                $tblTypeNew = $this->getTypeByNameAndDescription('Privat', 'Mobil');
                foreach ($tblPhoneToPersonList as $tblToPerson) {
                    $tblToPerson->setIsEmergencyContact(true);
                    $tblToPerson->setTblType($tblTypeNew);

                    $updateBulkList[] = $tblToPerson;
                }
            }
            if (($tblPhoneToCompanyList = $this->getPhoneToCompanyListByType($tblType))) {
                $tblTypeNew = $this->getTypeByNameAndDescription('Privat', 'Mobil');
                foreach ($tblPhoneToCompanyList as $tblToCompany) {
                    $tblToCompany->setIsEmergencyContact(true);
                    $tblToCompany->setTblType($tblTypeNew);

                    $updateBulkList[] = $tblToCompany;
                }
            }

            // Typ soft löschen
            $this->removeType($tblType);
        }

        if ($updateBulkList) {
            $this->updateEntityListBulk($updateBulkList);
        }
        if ($deleteBulkList) {
            $this->softRemoveEntityList($deleteBulkList);
        }
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblType
     */
    public function createType(
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblType')->findOneBy(array(
            TblType::ATTR_NAME        => $Name,
            TblType::ATTR_DESCRIPTION => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', $Id);
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPhone
     */
    public function getPhoneById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPhone', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getPhoneToPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson', $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getPhoneToCompanyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany', $Id);
    }

    /**
     * @return bool|TblPhone[]
     */
    public function getPhoneAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPhone');
    }

    /**
     * @param $Number
     *
     * @return TblPhone
     */
    public function createPhone($Number)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPhone')->findOneBy(array(
            TblPhone::ATTR_NUMBER => $Number
        ));
        if (null === $Entity) {
            $Entity = new TblPhone();
            $Entity->setNumber($Number);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getPhoneAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
                array(
                    TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId()
                ),
                array(
                    TblToPerson::ATT_TBL_TYPE => self::ORDER_ASC
                )
            );
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToPerson',
                array(
                    TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId()
                ),
                array(
                    TblToPerson::ATT_TBL_TYPE => self::ORDER_ASC
                ));
        }
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getPhoneAllByCompany(TblCompany $tblCompany)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblToCompany',
            array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblPhone $tblPhone
     * @param TblType $tblType
     * @param string $Remark
     * @param bool $isEmergencyContact
     *
     * @return TblToPerson
     */
    public function addPhoneToPerson(TblPerson $tblPerson, TblPhone $tblPhone, TblType $tblType, string $Remark, bool $isEmergencyContact = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToPerson')
            ->findOneBy(array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_PHONE      => $tblPhone->getId(),
                TblToPerson::ATT_TBL_TYPE       => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblToPerson();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblPhone($tblPhone);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Entity->setIsEmergencyContact($isEmergencyContact);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePhoneToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removePhoneToCompany(TblToCompany $tblToCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById('TblToCompany', $tblToCompany->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblPhone $tblPhone
     * @param TblType $tblType
     * @param string $Remark
     * @param bool $isEmergencyContact
     *
     * @return TblToCompany
     */
    public function addPhoneToCompany(TblCompany $tblCompany, TblPhone $tblPhone, TblType $tblType, string $Remark, bool $isEmergencyContact = false): TblToCompany
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblToCompany')
            ->findOneBy(array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_PHONE       => $tblPhone->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblToCompany();
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setTblPhone($tblPhone);
            $Entity->setTblType($tblType);
            $Entity->setRemark($Remark);
            $Entity->setIsEmergencyContact($isEmergencyContact);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return false|TblType
     */
    public function getTypeByNameAndDescription($Name, $Description)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblType', array(
            TblType::ATTR_NAME => $Name,
            TblType::ATTR_DESCRIPTION => $Description
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     *
     * @return false|TblToPerson[]
     */
    public function getPhoneToPersonAllBy(TblPerson $tblPerson, TblType $tblType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(
            TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToPerson::ATT_TBL_TYPE => $tblType->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblToPerson[]
     */
    public function getPhoneToPersonAllEmergencyContactByPerson(TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(
            TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToPerson::ATTR_IS_EMERGENCY_CONTACT => 1
        ));
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function restoreToPerson(TblToPerson $tblToPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPhone $tblPhone
     *
     * @return false|TblToPerson[]
     */
    public function getToPersonAllByPhone(TblPhone $tblPhone)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(TblToPerson::ATT_TBL_PHONE => $tblPhone->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblPhone $tblPhone
     *
     * @return false|TblToPerson
     */
    public function getPhoneToPersonByPersonAndPhone(TblPerson $tblPerson, TblPhone $tblPhone)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(
            TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblToPerson::ATT_TBL_PHONE => $tblPhone->getId()
        ));
    }

    /**
     * @param TblType $tblType
     *
     * @return bool
     */
    private function removeType(TblType $tblType): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblType', $tblType->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblType $tblType
     *
     * @return false|TblToPerson[]
     */
    private function getPhoneToPersonListByType(TblType $tblType)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToPerson', array(
            TblToPerson::ATT_TBL_TYPE => $tblType->getId()
        ));
    }

    /**
     * @param TblType $tblType
     *
     * @return false|TblToCompany[]
     */
    private function getPhoneToCompanyListByType(TblType $tblType)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblToCompany', array(
            TblToPerson::ATT_TBL_TYPE => $tblType->getId()
        ));
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {
            $Manager->bulkSaveEntity($tblElement);
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblElement, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function softRemoveEntityList(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {

            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            $Manager->removeEntity($Entity);
        }

        return true;
    }

    /**
     * @return array|false
     */
    private function getPhoneDuplicate()
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t.serviceTblPerson, t.tblPhone')
            ->from(TblToPerson::class, 't')
            ->groupBy('t.serviceTblPerson, t.tblPhone')
            ->having($queryBuilder->expr()->gt($queryBuilder->expr()->count('t.serviceTblPerson'), '?1'))
            ->setParameter(1, 1)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }
}
