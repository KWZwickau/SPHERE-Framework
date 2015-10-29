<?php
namespace SPHERE\Application\People\Relationship\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Relationship\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $tblGroupPerson = $this->createGroup('PERSON', 'Personenbeziehung', 'Person zu Person');
        $tblGroupCompany = $this->createGroup('COMPANY', 'Firmenbeziehungen', 'Person zu Firma');

        $this->createType('Sorgeberechtigt', '', $tblGroupPerson);
        $this->createType('Vormund', '', $tblGroupPerson);
        $this->createType('Bevollmächtigt', '', $tblGroupPerson);
        $this->createType('Geschwisterkind', '', $tblGroupPerson);
        $this->createType('Arzt', '', $tblGroupPerson);
        $this->createType('Ehepartner', '', $tblGroupPerson);
        $this->createType('Lebensabschnittsgefährte', '', $tblGroupPerson);

        $this->createType('Geschäftsführer', '', $tblGroupCompany);
    }

    /**
     * @param string $Identifier
     * @param string $Name
     * @param string $Description
     *
     * @return TblGroup
     */
    public function createGroup($Identifier, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblGroup')->findOneBy(array(
            TblGroup::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblGroup();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string        $Name
     * @param string        $Description
     * @param null|TblGroup $tblGroup
     * @param bool          $IsLocked
     *
     * @return TblType
     */
    public function createType($Name, $Description = '', TblGroup $tblGroup = null, $IsLocked = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblType')->findOneBy(array(
                TblType::ATTR_NAME      => $Name,
                TblType::ATTR_TBL_GROUP => ( $tblGroup ? $tblGroup->getId() : null ),
                TblType::ATTR_IS_LOCKED => $IsLocked
            ));
        } else {
            $Entity = $Manager->getEntity('TblType')->findOneBy(array(
                TblType::ATTR_NAME      => $Name,
                TblType::ATTR_TBL_GROUP => ( $tblGroup ? $tblGroup->getId() : null )
            ));
        }

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsLocked($IsLocked);
            $Entity->setTblGroup($tblGroup);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblGroup
     */
    public function getGroupByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup', array(
            TblGroup::ATTR_IDENTIFIER => $Identifier
        ));
    }

    /**
     * @return bool|TblGroup[]
     */
    public function getGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGroup');
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
     * @param TblGroup|null $tblGroup
     *
     * @return bool|TblType[]
     */
    public function getTypeAllByGroup(TblGroup $tblGroup = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', array(
            TblType::ATTR_TBL_GROUP => ( $tblGroup ? $tblGroup->getId() : null )
        ));
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getRelationshipToPersonById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblToPerson', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getRelationshipToCompanyById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblToCompany', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson(TblPerson $tblPerson)
    {

        $EntityList = array_merge(
            $this->getConnection()->getEntityManager()->getEntity('TblToPerson')->findBy(array(
                TblToPerson::SERVICE_TBL_PERSON_FROM => $tblPerson->getId()
            )),
            $this->getConnection()->getEntityManager()->getEntity('TblToPerson')->findBy(array(
                TblToPerson::SERVICE_TBL_PERSON_TO => $tblPerson->getId()
            ))
        );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getCompanyRelationshipAllByPerson(TblPerson $tblPerson)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblToCompany')->findBy(array(
            TblToCompany::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getCompanyRelationshipAllByCompany(TblCompany $tblCompany)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblToCompany')->findBy(array(
            TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId()
        ));
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPersonFrom
     * @param TblPerson $tblPersonTo
     * @param TblType   $tblType
     * @param string    $Remark
     *
     * @return TblToPerson
     */
    public function addPersonRelationshipToPerson(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblToPerson();
        $Entity->setServiceTblPersonFrom($tblPersonFrom);
        $Entity->setServiceTblPersonTo($tblPersonTo);
        $Entity->setTblType($tblType);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removePersonRelationshipToPerson(TblToPerson $tblToPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById('TblToPerson', $tblToPerson->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeCompanyRelationshipToPerson(TblToCompany $tblToCompany)
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
     * @param TblPerson  $tblPerson
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addCompanyRelationshipToPerson(
        TblCompany $tblCompany,
        TblPerson $tblPerson,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblToCompany();
        $Entity->setServiceTblCompany($tblCompany);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblType($tblType);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }
}
