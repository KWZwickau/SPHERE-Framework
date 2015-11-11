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
abstract class Agreement extends Student
{

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
    public function createStudentAgreementType(
        TblStudentAgreementCategory $tblStudentAgreementCategory,
        $Name,
        $Description = ''
    ) {

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
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

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
            ));
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
     * @return bool|TblStudentAgreementCategory[]
     */
    public function getStudentAgreementCategoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentAgreementCategory'
        );
    }
}
