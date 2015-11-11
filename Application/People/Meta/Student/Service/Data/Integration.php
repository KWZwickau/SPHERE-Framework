<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentIntegration;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Integration
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Integration extends Subject
{

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblStudentFocusType
     */
    public function createStudentFocusType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentFocusType')->findOneBy(array(
            TblStudentFocusType::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentFocusType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblStudentDisorderType
     */
    public function createStudentDisorderType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentDisorderType')->findOneBy(array(
            TblStudentDisorderType::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblStudentDisorderType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentIntegration
     */
    public function getStudentIntegrationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentIntegration', $Id
        );
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentFocus[]
     */
    public function getStudentFocusAllByStudent(TblStudent $tblStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentFocus', array(
                TblStudentFocus::ATTR_TBL_STUDENT => $tblStudent->getId()
            ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentFocusType
     */
    public function getStudentFocusTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentFocusType', $Id
        );
    }

    /**
     * @return bool|TblStudentFocusType[]
     */
    public function getStudentFocusTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentFocusType'
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentFocus
     */
    public function getStudentFocusById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentFocus', $Id
        );
    }

    /**
     * @return bool|TblStudentFocus[]
     */
    public function getStudentFocusAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentFocus'
        );
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentDisorder[]
     */
    public function getStudentDisorderAllByStudent(TblStudent $tblStudent)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentDisorder', array(
                TblStudentDisorder::ATTR_TBL_STUDENT => $tblStudent->getId()
            ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentDisorderType
     */
    public function getStudentDisorderTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentDisorderType', $Id
        );
    }

    /**
     * @return bool|TblStudentDisorderType[]
     */
    public function getStudentDisorderTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentDisorderType'
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentDisorder
     */
    public function getStudentDisorderById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentDisorder', $Id
        );
    }

    /**
     * @return bool|TblStudentDisorder[]
     */
    public function getStudentDisorderAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentDisorder'
        );
    }
}
