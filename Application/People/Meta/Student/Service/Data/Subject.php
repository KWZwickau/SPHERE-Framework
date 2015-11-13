<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Subject
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Subject extends Transfer
{

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentSubjectType
     */
    public function createStudentSubjectType($Identifier, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentSubjectType')->findOneBy(array(
            TblStudentSubjectType::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblStudentSubjectType();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentSubjectRanking
     */
    public function createStudentSubjectRanking($Identifier, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblStudentSubjectRanking')->findOneBy(array(
            TblStudentSubjectRanking::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblStudentSubjectRanking();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubject
     */
    public function getStudentSubjectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubject', $Id
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubjectType
     */
    public function getStudentSubjectTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectType', $Id
        );
    }

    /**
     * @return bool|TblStudentSubjectType[]
     */
    public function getStudentSubjectTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectType'
        );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentSubjectType
     */
    public function getStudentSubjectTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectType', array(
                TblStudentSubjectType::ATTR_IDENTIFIER => strtoupper($Identifier)
            ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubjectRanking
     */
    public function getStudentSubjectRankingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectRanking', $Id
        );
    }

    /**
     * @return bool|TblStudentSubjectRanking[]
     */
    public function getStudentSubjectRankingAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectRanking'
        );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentSubjectRanking
     */
    public function getStudentSubjectRankingByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentSubjectRanking', array(
                TblStudentSubjectRanking::ATTR_IDENTIFIER => strtoupper($Identifier)
            ));
    }
}
