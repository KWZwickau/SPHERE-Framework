<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Agreement
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Agreement extends Liberation
{

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentAgreement[]
     */
    public function getStudentAgreementAllByStudent(TblStudent $tblStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreement', array(
                TblStudentAgreement::ATTR_TBL_STUDENT => $tblStudent->getId()
            ));
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return false|TblStudentAgreement[]
     */
    public function getStudentAgreementAllByType(TblStudentAgreementType $tblStudentAgreementType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblStudentAgreement', array(
            TblStudentAgreement::ATTR_TBL_STUDENT_AGREEMENT_TYPE => $tblStudentAgreementType->getId(),
        ));
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     * @param TblStudent $tblStudent
     *
     * @return false|TblStudentAgreement
     */
    public function getStudentAgreementByTypeAndStudent(
        TblStudentAgreementType $tblStudentAgreementType,
        TblStudent $tblStudent
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentAgreement', array(
            TblStudentAgreement::ATTR_TBL_STUDENT_AGREEMENT_TYPE => $tblStudentAgreementType->getId(),
            TblStudentAgreement::ATTR_TBL_STUDENT => $tblStudent->getId()
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreement
     */
    public function getStudentAgreementById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreement', $Id
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreementType
     */
    public function getStudentAgreementTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementType', $Id
        );
    }

    /**
     * @param string                      $Name
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return false|TblStudentAgreementType
     */
    public function getStudentAgreementTypeByNameAndCategory($Name, TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementType',
            array(
                TblStudentAgreementType::ATTR_NAME => $Name,
                TblStudentAgreementType::ATTR_TBL_STUDENT_AGREEMENT_CATEGORY => $tblStudentAgreementCategory->getId()
            )
        );
    }

    /**
     * @return bool|TblStudentAgreementType[]
     */
    public function getStudentAgreementTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementType'
        );
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return bool|TblStudentAgreementType[]
     */
    public function getStudentAgreementTypeAllByCategory(TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementType', array(
                TblStudentAgreementType::ATTR_TBL_STUDENT_AGREEMENT_CATEGORY => $tblStudentAgreementCategory->getId()
            ), array('EntityCreate' => self::ORDER_ASC));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentAgreementCategory
     */
    public function getStudentAgreementCategoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementCategory', $Id
        );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblStudentAgreementCategory
     */
    public function getStudentAgreementCategoryByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementCategory',
            array(
                TblStudentAgreementCategory::ATTR_NAME => $Name
            )
        );
    }

    /**
     * @return bool|TblStudentAgreementCategory[]
     */
    public function getStudentAgreementCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementCategory', array('EntityCreate' => self::ORDER_ASC)
        );
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblStudentAgreementCategory
     */
    public function createStudentAgreementCategory($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentAgreementCategory')->findOneBy(array(
            TblStudentAgreementCategory::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentAgreementCategory();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return TblStudentAgreementType
     */
    public function createStudentAgreementType(TblStudentAgreementCategory $tblStudentAgreementCategory, $Name, $Description = '', $isUnlocked = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentAgreementType')->findOneBy(array(
            TblStudentAgreementType::ATTR_TBL_STUDENT_AGREEMENT_CATEGORY => $tblStudentAgreementCategory->getId(),
            TblStudentAgreementType::ATTR_NAME                           => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentAgreementType();
            $Entity->setTblStudentAgreementCategory($tblStudentAgreementCategory);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsUnlocked($isUnlocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudent              $tblStudent
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return TblStudentAgreement
     */
    public function addStudentAgreement(TblStudent $tblStudent, TblStudentAgreementType $tblStudentAgreementType) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblStudentAgreement $Entity */
        $Entity = $Manager->getEntity('TblStudentAgreement')->findOneBy(array(
            TblStudentAgreement::ATTR_TBL_STUDENT                => $tblStudent->getId(),
            TblStudentAgreement::ATTR_TBL_STUDENT_AGREEMENT_TYPE => $tblStudentAgreementType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblStudentAgreement();
            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentAgreementType($tblStudentAgreementType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     * @param string                      $Name
     * @param string                      $Description
     *
     * @return bool
     */
    public function updateStudentAgreementCategory(TblStudentAgreementCategory $tblStudentAgreementCategory, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentAgreementCategory $Entity */
        $Entity = $Manager->getEntityById('TblStudentAgreementCategory', $tblStudentAgreementCategory->getId());
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
     * @param TblStudentAgreementType $tblStudentAgreementType
     * @param string                  $Name
     * @param string                  $Description
     *
     * @return bool
     */
    public function updateStudentAgreementType(TblStudentAgreementType $tblStudentAgreementType, $Name, $Description = '', $isUnlocked = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentAgreementType $Entity */
        $Entity = $Manager->getEntityById('TblStudentAgreementType', $tblStudentAgreementType->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setIsUnlocked($isUnlocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }
        return false;
    }

    /**
     * @param TblStudentAgreement $tblStudentAgreement
     *
     * @return bool
     */
    public function removeStudentAgreement(TblStudentAgreement $tblStudentAgreement)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentAgreement $Entity */
        $Entity = $Manager->getEntityById('TblStudentAgreement', $tblStudentAgreement->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudentAgreementCategory $tblStudentAgreementCategory
     *
     * @return bool
     */
    public function destroyStudentAgreementCategory(TblStudentAgreementCategory $tblStudentAgreementCategory)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentAgreementCategory $Entity */
        $Entity = $Manager->getEntityById('TblStudentAgreementCategory', $tblStudentAgreementCategory->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return bool
     */
    public function destroyStudentAgreementType(TblStudentAgreementType $tblStudentAgreementType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentAgreementType $Entity */
        $Entity = $Manager->getEntityById('TblStudentAgreementType', $tblStudentAgreementType->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
