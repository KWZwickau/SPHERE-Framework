<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:51
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\SerialLetter\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createType('Person', 'PERSON');
        $this->createType('Sorgeberechtigter', 'CUSTODY');
        $this->createType('Familie', 'FAMILY');
    }

    /**
     * @param $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter',
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
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson $tblPerson
     * @param TblToPerson $tblToPerson
     * @param TblType $tblType
     *
     * @return bool|TblAddressPerson
     */
    public function getAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblToPerson $tblToPerson,
        TblType $tblType
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAddressPerson', array(
            TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
            TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON => $tblToPerson->getId(),
            TblAddressPerson::ATTR_TBL_TYPE => $tblType->getId()
        ) );
    }

    /**
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
     * @param $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', $Id);
    }

    /**
     * @param $Identifier
     * @return bool|TblType
     */
    public function getTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', array(
            TblType::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblType
     */
    public function createType(
        $Name,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblType')
            ->findOneBy(array(
                TblType::ATTR_NAME => $Name,
                TblType::ATTR_IDENTIFIER => $Identifier
            ));

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Name
     * @param TblGroup $tblGroup
     * @param string $Description
     *
     * @return TblSerialLetter
     */
    public function createSerialLetter(
        $Name,
        TblGroup $tblGroup,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSerialLetter')
            ->findOneBy(array(
                TblSerialLetter::ATTR_NAME => $Name,
                TblSerialLetter::ATTR_SERVICE_TBL_GROUP => $tblGroup->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblSerialLetter();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setServiceTblGroup($tblGroup);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     * @param TblPerson $tblPerson
     * @param TblToPerson $tblToPerson
     * @param TblType $tblType
     *
     * @return TblAddressPerson
     */
    public function createAddressPerson(
        TblSerialLetter $tblSerialLetter,
        TblPerson $tblPerson,
        TblToPerson $tblToPerson = null,
        TblType $tblType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblAddressPerson')
            ->findOneBy(array(
                TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblAddressPerson::ATTR_SERVICE_TBL_TO_PERSON => $tblToPerson ? $tblToPerson->getId() : null,
                TblAddressPerson::ATTR_TBL_TYPE => $tblType->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblAddressPerson();
            $Entity->setTblSerialLetter($tblSerialLetter);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblToPerson($tblToPerson);
            $Entity->setTblType($tblType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSerialLetter $tblSerialLetter
     *
     * @return bool
     */
    public function destroyAddressPersonAllBySerialLetter(TblSerialLetter $tblSerialLetter)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblAddressPerson')
            ->findBy(array(TblAddressPerson::ATTR_TBL_SERIAL_LETTER => $tblSerialLetter->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        return true;
    }
}