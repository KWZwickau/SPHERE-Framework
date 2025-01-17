<?php
namespace SPHERE\Application\Reporting\SerialLetter\Service;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\SerialLetter\SerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterCategory;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblFilterField;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialCompany;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialPerson;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\SerialLetter\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_PERSON_GROUP);
        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_PERSON_GROUP_STUDENT);
        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_PERSON_GROUP_PROSPECT);
        $this->createFilterCategory(TblFilterCategory::IDENTIFIER_COMPANY);
    }

    /**
     * @param string $Name
     *
     * @return null|TblFilterCategory
     */
    public function createFilterCategory($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFilterCategory')
            ->findOneBy(array(
                TblFilterCategory::ATTR_NAME => $Name,
            ));

        if (null === $Entity) {
            $Entity = new TblFilterCategory();
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblFilterCategory $tblFilterCategory
     * @param string            $Name
     *
     * @return \Doctrine\ORM\Mapping\Entity|object|TblFilterCategory
     */
    public function updateFilterCategory(TblFilterCategory $tblFilterCategory, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblFilterCategory', $tblFilterCategory->getId());

        if ($Entity != null) {
            /** @var TblFilterCategory $Entity */
            $Protocol = clone $Entity;

            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter',
            $Id);
    }

    /**
     * @param string $Name
     *
     * @return false|TblSerialLetter
     */
    public function getSerialLetterByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter',
            array(TblSerialLetter::ATTR_NAME => $Name));
    }

    /**
     * @param int $Id
     *
     * @return false|TblSerialPerson
     */
    public function getSerialPersonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSerialCompany
     */
    public function getSerialCompanyById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
            $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblFilterCategory
     */
    public function getFilterCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterCategory',
            $Id);
    }

    /**
     * @return bool|TblSerialLetter[]
     */
    public function getSerialLetterAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter');
    }

    /**
     * @return false|TblFilterCategory[]
     */
    public function getFilterCategoryAll()
    {

        return $this->getForceEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterCategory');
    }

    /**
     * @return false|TblFilterField[]
     */
    public function getFilterFieldAll()
    {

        return $this->getForceEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterField');
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblFilterField[]
     */
    public function getFilterFieldAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterField',
            array(
                TblFilterField::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()
            ));
    }

    /**
     * @param int $FieldNumber
     *
     * @return false|TblFilterField[]
     */
    public function getFilterFieldListByFilterFieldNumber($FieldNumber)
    {

        return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterField',
            array(TblFilterField::ATTR_FILTER_NUMBER => $FieldNumber));
    }

//    /**
//     * @param TblSerialLetter   $tblSerialLetter
//     * @param TblFilterCategory $tblFilterCategory
//     *
//     * @return false|TblFilterField[]
//     */
//    public function getFilterFieldActiveAllBySerialLetter(TblSerialLetter $tblSerialLetter, TblFilterCategory $tblFilterCategory)
//    {
//
//        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterField',
//            array(
//                TblFilterField::ATTR_TBL_SERIAL_LETTER   => $tblSerialLetter->getId(),
//                TblFilterField::ATTR_TBL_FILTER_CATEGORY => $tblFilterCategory->getId()
//            ));
//    }

    /**
     * @param $Name
     *
     * @return false|TblFilterCategory
     */
    public function getFilterCategoryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFilterCategory',
            array(TblFilterCategory::ATTR_NAME => $Name));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|int
     */
    public function getSerialLetterCount(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson',
            array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return false|TblSerialPerson[]
     */
    public function getSerialPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            array(TblSerialPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return array
     */
    public function getSerialPersonIdListBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $SerialLetter = new TblSerialLetter();
        $SerialPerson = new TblSerialPerson();

        $Builder = $Manager->getQueryBuilder();
        $Query = $Builder->select('tSP.serviceTblPerson as PersonId')
            ->from($SerialLetter->getEntityFullName(), 'tSL')
            ->leftJoin($SerialPerson->getEntityFullName(), 'tSP', 'WITH', 'tSP.tblSerialLetter = tSL.Id')
            ->where($Builder->expr()->in('tSL.Id', $tblSerialLetter->getId()))
            ->getQuery();
        $result = $Query->getResult();
        $IdCorrectedResult = array();
        if(!empty($result)){
            foreach($result as $row){
                if($row['PersonId']){
                    $IdCorrectedResult[] = $row['PersonId'];
                }
            }
        }
        return $IdCorrectedResult;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param null            $isIgnore
     *
     * @return false|TblSerialCompany[]
     */
    public function getSerialCompanyBySerialLetter(TblSerialLetter $tblSerialLetter, $isIgnore = null)
    {

        if($isIgnore === null){
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
                array(
                    TblSerialCompany::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
                array(
                    TblSerialCompany::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                    TblSerialCompany::ATTR_IS_IGNORE => $isIgnore
                ));
        }

    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblCompany      $tblCompany
     * @param bool|null       $isIgnore
     *
     * @return TblSerialCompany[]|bool
     */
    public function getSerialCompanyByCompany(TblSerialLetter $tblSerialLetter, TblCompany $tblCompany, $isIgnore = null)
    {

        if($isIgnore === null){
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
                array(
                    TblSerialCompany::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                    TblSerialCompany::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId()
                ));
        } else {
            return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
                array(
                    TblSerialCompany::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                    TblSerialCompany::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId(),
                    TblSerialCompany::ATTR_IS_IGNORE => $isIgnore
                ));
        }
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblCompany      $tblCompany
     * @param TblPerson|null  $tblPerson
     *
     * @return false|TblSerialCompany[]
     */
    public function getSerialCompanyBySerialLetterAndCompanyAndPerson(TblSerialLetter $tblSerialLetter, TblCompany $tblCompany,
        TblPerson $tblPerson = null)
    {

        if($tblPerson){
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
                array(
                    TblSerialCompany::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                    TblSerialCompany::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId(),
                    TblSerialCompany::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialCompany',
                array(
                    TblSerialCompany::ATTR_TBL_SERIAL_LETTER   => $tblSerialLetter->getId(),
                    TblSerialCompany::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId(),
                    TblSerialCompany::ATTR_SERVICE_TBL_PERSON => null,
                ));
        }
    }

    /** @deprecated
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return false|TblSerialPerson
     */
    public function getSerialPersonBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return TblPerson[]|array
     */
    public function getPersonBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialPerson',
            array(TblSerialPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        $tblPersonList = array();
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                /** @var TblSerialPerson $Entity */
                $tblPersonList[] = $Entity->getServiceTblPerson();
            }
        }
        $tblPersonList = array_filter($tblPersonList);

        return $tblPersonList;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetterAndPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson', array(
            TblAddressPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
            TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /** @deprecated
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool|TblAddressPerson[]
     */
    public function getAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson', array(
            TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()
        ));
    }

    /**
     * @param string                 $Name
     * @param string                 $Description
     * @param null|TblFilterCategory $tblFilterCategory
     *
     * @return TblSerialLetter
     */
    public function createSerialLetter(
        $Name,
        $Description = '',
        TblFilterCategory $tblFilterCategory = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialLetter')
            ->findOneBy(array(TblSerialLetter::ATTR_NAME => $Name,));

        if (null === $Entity) {
            $Entity = new TblSerialLetter();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            if ($tblFilterCategory !== null) {
                $Entity->setFilterCategory($tblFilterCategory);
            }

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter   $tblSerialLetter
     * @param TblFilterCategory $tblFilterCategory
     * @param string            $FilterField
     * @param string            $FilterValue
     * @param int               $FilterNumber
     *
     * @return TblFilterField
     */
    public function createFilterField(
        TblSerialLetter $tblSerialLetter,
        TblFilterCategory $tblFilterCategory,
        $FilterField,
        $FilterValue,
        $FilterNumber
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblFilterField')
            ->findOneBy(array(
                TblFilterField::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                TblFilterField::ATTR_FIELD             => $FilterField,
//                TblFilterField::ATTR_VALUE             => $FilterValue
                TblFilterField::ATTR_FILTER_NUMBER     => $FilterNumber
            ));

        /** @var TblFilterField $Entity */
        if ($Entity === null) {
            $Entity = new TblFilterField();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setFilterCategory($tblFilterCategory);
            $Entity->setField($FilterField);
            $Entity->setValue($FilterValue);
            $Entity->setFilterNumber($FilterNumber);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            $Protocol = clone $Entity;
            $Entity->setFilterCategory($tblFilterCategory);
            $Entity->setValue($FilterValue);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return null|object|TblSerialPerson
     */
    public function addSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialPerson')
            ->findOneBy(array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblSerialPerson();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $tblPersonList
     */
    public function addSerialPersonBulk(TblSerialLetter $tblSerialLetter, $tblPersonList)
    {

        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblPersonList as $tblPerson) {
            $Entity = $Manager->getEntity('TblSerialPerson')
                ->findOneBy(array(
                    TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                    TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                ));

            if (null === $Entity) {
                $Entity = new TblSerialPerson();
                $Entity->setTblSerialLetter($tblSerialLetter);
                $Entity->setServiceTblPerson($tblPerson);

                $Manager->bulkSaveEntity($Entity);
                Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $TableResult
     */
    public function addSerialCompanyBulk(TblSerialLetter $tblSerialLetter, array $TableResult)
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($TableResult as $Key => $row) {
            if(($tblCompany = Company::useService()->getCompanyById($row['CompanyId']))) {
                // set Ignore if no Address
                $isIgnore = true;
                if(($tblAddress = Address::useService()->getAddressByCompany($tblCompany))){
                    $isIgnore = false;
                }
                if($row['PersonId'] != ''){
                    if(($tblPerson = Person::useService()->getPersonById($row['PersonId']))){
                        $Entity = $Manager->getEntity('TblSerialCompany')
                            ->findOneBy(array(
                                TblSerialCompany::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                                TblSerialCompany::ATTR_SERVICE_TBL_COMPANY  => $tblCompany->getId(),
                                TblSerialCompany::ATTR_SERVICE_TBL_PERSON  => $tblPerson->getId(),
                            ));
                        if (null === $Entity) {
                            $Entity = new TblSerialCompany();
                            $Entity->setTblSerialLetter($tblSerialLetter);
                            $Entity->setServiceTblCompany($tblCompany);
                            $Entity->setServiceTblPerson($tblPerson);
                            $Entity->setIsIgnore($isIgnore);

                            $Manager->bulkSaveEntity($Entity);
                            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                        }
                    }
                } else {
                    $Entity = $Manager->getEntity('TblSerialCompany')
                        ->findOneBy(array(
                            TblSerialCompany::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                            TblSerialCompany::ATTR_SERVICE_TBL_COMPANY  => $tblCompany->getId(),
                        ));
                    if (null === $Entity) {
                        $Entity = new TblSerialCompany();
                        $Entity->setTblSerialLetter($tblSerialLetter);
                        $Entity->setServiceTblCompany($tblCompany);
                        $Entity->setIsIgnore($isIgnore);

                        $Manager->bulkSaveEntity($Entity);
                        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                    }
                }
            }
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param string          $Name
     * @param string          $Description
     *
     * @return false|TblSerialLetter
     */
    public function updateSerialLetter(TblSerialLetter $tblSerialLetter, string $Name, string $Description)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSerialLetter $Entity */
        $Entity = $Manager->getEntityById('TblSerialLetter', $tblSerialLetter->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return $Entity;
        }

        return false;
    }

    /**
     * @param TblSerialCompany $tblSerialCompany
     * @param                  $IsIgnore
     *
     * @return bool
     */
    public function changeSerialCompanyStatus(TblSerialCompany $tblSerialCompany, $IsIgnore)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSerialCompany $Entity */
        $Entity = $Manager->getEntityById('TblSerialCompany', $tblSerialCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsIgnore($IsIgnore);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }
        return false;
    }

    /**
     * @param TblSerialLetter    $tblSerialLetter
     * @param TblPerson          $tblPerson
     * @param TblPerson          $tblPersonToAddress
     * @param TblToPerson        $tblToPerson
     * @param TblToCompany       $tblToCompany
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblPerson $tblPersonToAddress,
        TblToPerson $tblToPerson = null,
        TblToCompany $tblToCompany = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($tblToCompany !== null) {
            $Entity = $Manager->getEntity('TblAddressPerson')
                ->findOneBy(array(
                    TblAddressPerson::ATTR_TBL_SERIAL_LETTER             => $tblSerialLetter->getId(),
                    TblAddressPerson::ATTR_SERVICE_TBL_PERSON            => $tblPerson->getId(),
                    TblAddressPerson::ATTR_SERVICE_TBL_PERSON_TO_ADDRESS => $tblPersonToAddress->getId(),
                    TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON         => $tblToCompany->getId()
                ));
        } else {
            $Entity = $Manager->getEntity('TblAddressPerson')
                ->findOneBy(array(
                    TblAddressPerson::ATTR_TBL_SERIAL_LETTER             => $tblSerialLetter->getId(),
                    TblAddressPerson::ATTR_SERVICE_TBL_PERSON            => $tblPerson->getId(),
                    TblAddressPerson::ATTR_SERVICE_TBL_PERSON_TO_ADDRESS => $tblPersonToAddress->getId(),
                    TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON         => $tblToPerson ? $tblToPerson->getId() : null
                ));
        }

        if (null === $Entity) {
            $Entity = new TblAddressPerson();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblPersonToAddress($tblPersonToAddress);
            $Entity->setServiceTblToPerson($tblToPerson, $tblToCompany);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param array $PersonListArray
     * @param bool  $isCompany
     *
     * @return bool
     */
    public function createAddressPersonList(
        $PersonListArray = array(),
        $isCompany = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($isCompany) {
            $tblToPerson = null;
            if (!empty($PersonListArray)) {
                foreach ($PersonListArray as $SerialLetterId => $tblPersonList) {
                    $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($SerialLetterId);
                    if ($tblSerialLetter) {
                        foreach ($tblPersonList as $PersonId => $tblPersonToList) {
                            $tblPerson = Person::useService()->getPersonById($PersonId);
                            if ($tblPerson) {
                                /** @var TblToCompany $tblToCompany */
                                foreach ($tblPersonToList as $PersonToId => $tblToCompany) {
                                    $tblPersonTo = Person::useService()->getPersonById($PersonToId);
                                    if ($tblPersonTo && $tblToCompany) {
                                        $Entity = $Manager->getEntity('TblAddressPerson')
                                            ->findOneBy(array(
                                                TblAddressPerson::ATTR_TBL_SERIAL_LETTER             => $tblSerialLetter->getId(),
                                                TblAddressPerson::ATTR_SERVICE_TBL_PERSON            => $tblPerson->getId(),
                                                TblAddressPerson::ATTR_SERVICE_TBL_PERSON_TO_ADDRESS => $tblPersonTo->getId(),
                                                TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON         => $tblToCompany->getId(),
                                            ));
                                        if (null === $Entity) {
                                            $Entity = new TblAddressPerson();
                                            $Entity->setTblSerialLetter($tblSerialLetter);
                                            $Entity->setServiceTblPerson($tblPerson);
                                            $Entity->setServiceTblPersonToAddress($tblPersonTo);
                                            $Entity->setServiceTblToPerson($tblToPerson, $tblToCompany);

                                            $Manager->bulkSaveEntity($Entity);
                                            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $tblToCompany = null;
            if (!empty($PersonListArray)) {
                foreach ($PersonListArray as $SerialLetterId => $tblPersonList) {
                    $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($SerialLetterId);
                    if ($tblSerialLetter) {
                        foreach ($tblPersonList as $PersonId => $tblPersonToList) {
                            $tblPerson = Person::useService()->getPersonById($PersonId);
                            if ($tblPerson) {
                                /** @var TblToPerson $tblToPerson */
                                foreach ($tblPersonToList as $PersonToId => $tblToPerson) {
                                    $tblPersonTo = Person::useService()->getPersonById($PersonToId);
                                    if ($tblPersonTo && $tblToPerson) {
                                        $Entity = $Manager->getEntity('TblAddressPerson')
                                            ->findOneBy(array(
                                                TblAddressPerson::ATTR_TBL_SERIAL_LETTER             => $tblSerialLetter->getId(),
                                                TblAddressPerson::ATTR_SERVICE_TBL_PERSON            => $tblPerson->getId(),
                                                TblAddressPerson::ATTR_SERVICE_TBL_PERSON_TO_ADDRESS => $tblPersonTo->getId(),
                                                TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON         => $tblToPerson->getId()
                                            ));
                                        if (null === $Entity) {
                                            $Entity = new TblAddressPerson();
                                            $Entity->setTblSerialLetter($tblSerialLetter);
                                            $Entity->setServiceTblPerson($tblPerson);
                                            $Entity->setServiceTblPersonToAddress($tblPersonTo);
                                            $Entity->setServiceTblToPerson($tblToPerson, $tblToCompany);

                                            $Manager->bulkSaveEntity($Entity);
                                            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblAddressPerson')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        }

        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialCompanyBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblSerialCompany')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        }

        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyFilterFiled(TblFilterField $tblFilterField)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var Element $Entity */
        $Entity = $Manager->getEntity('TblFilterField')->find($tblFilterField->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
        }
        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyFilterFiledAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblFilterField')
            ->findBy(array(TblFilterField::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();
        }

        return true;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetterAndPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {
        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblAddressPerson')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                           TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        return true;
    }

    /**
     * @param TblAddressPerson $tblAddressPerson
     *
     * @return bool
     */
    public function destroyAddressPerson(TblAddressPerson $tblAddressPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblAddressPerson', $tblAddressPerson->getId());
        /** @var TblAddressPerson $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSerialLetter $Entity */
        $Entity = $Manager->getEntityById('TblSerialLetter', $tblSerialLetter->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson       $tblPerson
     *
     * @return bool
     */
    public function removeSerialPerson(TblSerialLetter $tblSerialLetter, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialPerson')
            ->findOneBy(array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        /** @var TblSerialPerson $Entity */
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param array           $PersonIdRemoveList
     */
    public function removeSerialPersonBulk(TblSerialLetter $tblSerialLetter, $PersonIdRemoveList)
    {

        $Manager = $this->getConnection()->getEntityManager();

        foreach ($PersonIdRemoveList as $PersonId) {
            $Entity = $Manager->getEntity('TblSerialPerson')
                ->findOneBy(array(
                    TblSerialPerson::ATTR_TBL_SERIAL_LETTER  => $tblSerialLetter->getId(),
                    TblSerialPerson::ATTR_SERVICE_TBL_PERSON => $PersonId,
                ));

            /** @var TblSerialPerson $Entity */
            if (null !== $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param $SerialCompanyDivList
     *
     * @return void
     */
    public function removeSerialCompanyBulk($SerialCompanyDivList)
    {

        $Manager = $this->getConnection()->getEntityManager();

        foreach ($SerialCompanyDivList as $SerialCompanyId) {
            $Entity = $Manager->getEntityById('TblSerialCompany', $SerialCompanyId);

            /** @var TblSerialPerson $Entity */
            if (null !== $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
        }
        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     */
    public function destroySerialPerson(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblSerialPerson')
            ->findBy(array(
                TblSerialPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
        }
        $Manager->flushCache();
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     */
    public function destroySerialCompany(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityList = $Manager->getEntity('TblSerialCompany')
            ->findBy(array(
                TblSerialCompany::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
                $Manager->bulkKillEntity($Entity);
            }
        }
        $Manager->flushCache();
    }
}