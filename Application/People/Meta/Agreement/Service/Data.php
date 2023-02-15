<?php
namespace SPHERE\Application\People\Meta\Agreement\Service;

use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreement;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementCategory;
use SPHERE\Application\People\Meta\Agreement\Service\Entity\TblPersonAgreementType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Agreement\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        // Werte nur bei der Initialisierung verwenden
        if(!$this->getPersonAgreementCategoryAll()){
            $tblPersonAgreementCategory = $this->createPersonAgreementCategory(
                'Foto der Person',
                'Sowohl Einzelaufnahmen als auch in Gruppen (z.B. zufällig)'
            );
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'in Schulschriften');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'in Veröffentlichungen');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'auf Internetpräsenz');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'auf Facebookseite');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'für Druckpresse');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'durch Ton/Video/Film');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'für Werbung in eigener Sache');

            $tblPersonAgreementCategory = $this->createPersonAgreementCategory(
                'Namentliche Erwähnung der Person',
                ''
            );
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'in Schulschriften');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'in Veröffentlichungen');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'auf Internetpräsenz');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'auf Facebookseite');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'für Druckpresse');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'durch Ton/Video/Film');
            $this->createPersonAgreementType($tblPersonAgreementCategory, 'für Werbung in eigener Sache');
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblPersonAgreement[]
     */
    public function getPersonAgreementAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreement', array(
                TblPersonAgreement::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     *
     * @return false|TblPersonAgreement[]
     */
    public function getPersonAgreementAllByType(TblPersonAgreementType $tblPersonAgreementType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblPersonAgreement', array(
            TblPersonAgreement::ATTR_TBL_PERSON_AGREEMENT_TYPE => $tblPersonAgreementType->getId(),
        ));
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     * @param TblPerson $tblPerson
     *
     * @return false|TblPersonAgreement
     */
    public function getPersonAgreementByTypeAndPerson(
        TblPersonAgreementType $tblPersonAgreementType,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblPersonAgreement', array(
            TblPersonAgreement::ATTR_TBL_PERSON_AGREEMENT_TYPE => $tblPersonAgreementType->getId(),
            TblPersonAgreement::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblPersonAgreement
     */
    public function getPersonAgreementById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreement', $Id
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblPersonAgreementType
     */
    public function getPersonAgreementTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementType', $Id
        );
    }

    /**
     * @param string                     $Name
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     *
     * @return bool|TblPersonAgreementType
     */
    public function getPersonAgreementTypeByNameAndCategory($Name, TblPersonAgreementCategory $tblPersonAgreementCategory)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementType',
            array(
                TblPersonAgreementType::ATTR_NAME => $Name,
                TblPersonAgreementType::ATTR_TBL_PERSON_AGREEMENT_CATEGORY => $tblPersonAgreementCategory->getId()
            )
        );
    }

    /**
     * @return bool|TblPersonAgreementType[]
     */
    public function getPersonAgreementTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementType'
        );
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     *
     * @return bool|TblPersonAgreementType[]
     */
    public function getPersonAgreementTypeAllByCategory(TblPersonAgreementCategory $tblPersonAgreementCategory)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementType', array(
                TblPersonAgreementType::ATTR_TBL_PERSON_AGREEMENT_CATEGORY => $tblPersonAgreementCategory->getId()
            ), array('EntityCreate' => self::ORDER_ASC));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblPersonAgreementCategory
     */
    public function getPersonAgreementCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementCategory', $Id
        );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPersonAgreementCategory
     */
    public function getPersonAgreementCategoryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementCategory',
            array(
                TblPersonAgreementCategory::ATTR_NAME => $Name
            )
        );
    }

    /**
     * @return bool|TblPersonAgreementCategory[]
     */
    public function getPersonAgreementCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblPersonAgreementCategory', array('EntityCreate' => self::ORDER_ASC)
        );
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblPersonAgreementCategory
     */
    public function createPersonAgreementCategory($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPersonAgreementCategory')->findOneBy(array(
            TblPersonAgreementCategory::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblPersonAgreementCategory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return TblPersonAgreementType
     */
    public function createPersonAgreementType(TblPersonAgreementCategory $tblPersonAgreementCategory, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPersonAgreementType')->findOneBy(array(
            TblPersonAgreementType::ATTR_TBL_PERSON_AGREEMENT_CATEGORY => $tblPersonAgreementCategory->getId(),
            TblPersonAgreementType::ATTR_NAME                           => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblPersonAgreementType();
            $Entity->setTblPersonAgreementCategory($tblPersonAgreementCategory);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson              $tblPerson
     * @param TblPersonAgreementType $tblPersonAgreementType
     *
     * @return TblPersonAgreement
     */
    public function addPersonAgreement(TblPerson $tblPerson, TblPersonAgreementType $tblPersonAgreementType) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblPersonAgreement $Entity */
        $Entity = $Manager->getEntity('TblPersonAgreement')->findOneBy(array(
            TblPersonAgreement::ATTR_SERVICE_TBL_PERSON        => $tblPerson->getId(),
            TblPersonAgreement::ATTR_TBL_PERSON_AGREEMENT_TYPE => $tblPersonAgreementType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblPersonAgreement();
            $Entity->setserviceTblPerson($tblPerson);
            $Entity->setTblPersonAgreementType($tblPersonAgreementType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return bool
     */
    public function updatePersonAgreementCategory(TblPersonAgreementCategory $tblPersonAgreementCategory, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblPersonAgreementCategory $Entity */
        $Entity = $Manager->getEntityById('TblPersonAgreementCategory', $tblPersonAgreementCategory->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     * @param string                  $Name
     * @param string                  $Description
     *
     * @return bool
     */
    public function updatePersonAgreementType(TblPersonAgreementType $tblPersonAgreementType, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblPersonAgreementType $Entity */
        $Entity = $Manager->getEntityById('TblPersonAgreementType', $tblPersonAgreementType->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }
        return false;
    }

    /**
     * @param TblPersonAgreement $tblPersonAgreement
     *
     * @return bool
     */
    public function removePersonAgreement(TblPersonAgreement $tblPersonAgreement)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPersonAgreement $Entity */
        $Entity = $Manager->getEntityById('TblPersonAgreement', $tblPersonAgreement->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPersonAgreementCategory $tblPersonAgreementCategory
     *
     * @return bool
     */
    public function destroyPersonAgreementCategory(TblPersonAgreementCategory $tblPersonAgreementCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPersonAgreementCategory $Entity */
        $Entity = $Manager->getEntityById('TblPersonAgreementCategory', $tblPersonAgreementCategory->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPersonAgreementType $tblPersonAgreementType
     *
     * @return bool
     */
    public function destroyPersonAgreementType(TblPersonAgreementType $tblPersonAgreementType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPersonAgreementType $Entity */
        $Entity = $Manager->getEntityById('TblPersonAgreementType', $tblPersonAgreementType->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
