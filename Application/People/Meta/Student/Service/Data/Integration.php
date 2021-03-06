<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentIntegration;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
     * @param $Name
     * @return bool|TblStudentFocusType
     */
    public function getStudentFocusTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentFocusType', array(TblStudentFocusType::ATTR_NAME => $Name)
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
     * @param $Name
     * @return bool|TblStudentDisorderType
     */
    public function getStudentDisorderTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblStudentDisorderType', array(TblStudentDisorderType::ATTR_NAME => $Name)
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

    /**
     * @param TblPerson|null  $tblPerson
     * @param TblCompany|null $tblCompany
     * @param                 $CoachingRequestDate
     * @param                 $CoachingCounselDate
     * @param                 $CoachingDecisionDate
     * @param                 $CoachingRequired
     * @param                 $CoachingTime
     * @param                 $CoachingRemark
     *
     * @return TblStudentIntegration
     */
    public function createStudentIntegration(
        TblPerson $tblPerson = null,
        TblCompany $tblCompany = null,
        $CoachingRequestDate,
        $CoachingCounselDate,
        $CoachingDecisionDate,
        $CoachingRequired,
        $CoachingTime,
        $CoachingRemark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblStudentIntegration();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblCompany($tblCompany);
        $Entity->setCoachingRequestDate(( $CoachingRequestDate ? new \DateTime($CoachingRequestDate) : null ));
        $Entity->setCoachingCounselDate(( $CoachingCounselDate ? new \DateTime($CoachingCounselDate) : null ));
        $Entity->setCoachingDecisionDate(( $CoachingDecisionDate ? new \DateTime($CoachingDecisionDate) : null ));
        $Entity->setCoachingRequired($CoachingRequired);
        $Entity->setCoachingTime($CoachingTime);
        $Entity->setCoachingRemark($CoachingRemark);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentIntegration $tblStudentIntegration
     * @param TblPerson|null        $tblPerson
     * @param TblCompany|null       $tblCompany
     * @param                       $CoachingRequestDate
     * @param                       $CoachingCounselDate
     * @param                       $CoachingDecisionDate
     * @param                       $CoachingRequired
     * @param                       $CoachingTime
     * @param                       $CoachingRemark
     *
     * @return bool
     */
    public function updateStudentIntegration(
        TblStudentIntegration $tblStudentIntegration,
        TblPerson $tblPerson = null,
        TblCompany $tblCompany = null,
        $CoachingRequestDate,
        $CoachingCounselDate,
        $CoachingDecisionDate,
        $CoachingRequired,
        $CoachingTime,
        $CoachingRemark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblStudentIntegration $Entity */
        $Entity = $Manager->getEntityById('TblStudentIntegration', $tblStudentIntegration->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setCoachingRequestDate(( $CoachingRequestDate ? new \DateTime($CoachingRequestDate) : null ));
            $Entity->setCoachingCounselDate(( $CoachingCounselDate ? new \DateTime($CoachingCounselDate) : null ));
            $Entity->setCoachingDecisionDate(( $CoachingDecisionDate ? new \DateTime($CoachingDecisionDate) : null ));
            $Entity->setCoachingRequired($CoachingRequired);
            $Entity->setCoachingTime($CoachingTime);
            $Entity->setCoachingRemark($CoachingRemark);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudent             $tblStudent
     * @param TblStudentDisorderType $tblStudentDisorderType
     *
     * @return TblStudentDisorder
     */
    public function addStudentDisorder(
        TblStudent $tblStudent,
        TblStudentDisorderType $tblStudentDisorderType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblStudentDisorder $Entity */
        $Entity = $Manager->getEntity('TblStudentDisorder')->findOneBy(array(
            TblStudentDisorder::ATTR_TBL_STUDENT               => $tblStudent->getId(),
            TblStudentDisorder::ATTR_TBL_STUDENT_DISORDER_TYPE => $tblStudentDisorderType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblStudentDisorder();
            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentDisorderType($tblStudentDisorderType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudentDisorder $tblStudentDisorder
     *
     * @return bool
     */
    public function removeStudentDisorder(TblStudentDisorder $tblStudentDisorder)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentDisorder $Entity */
        $Entity = $Manager->getEntityById('TblStudentDisorder', $tblStudentDisorder->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentFocusType $tblStudentFocusType
     * @param bool $IsPrimary
     *
     * @return TblStudentFocus
     */
    public function addStudentFocus(
        TblStudent $tblStudent,
        TblStudentFocusType $tblStudentFocusType,
        $IsPrimary = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblStudentFocus $Entity */
        $Entity = $Manager->getEntity('TblStudentFocus')->findOneBy(array(
            TblStudentFocus::ATTR_TBL_STUDENT            => $tblStudent->getId(),
            TblStudentFocus::ATTR_TBL_STUDENT_FOCUS_TYPE => $tblStudentFocusType->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblStudentFocus();
            $Entity->setTblStudent($tblStudent);
            $Entity->setTblStudentFocusType($tblStudentFocusType);
            $Entity->setIsPrimary($IsPrimary);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } elseif ($IsPrimary != $Entity->isPrimary()) {
            $Protocol = clone $Entity;
            $Entity->setIsPrimary($IsPrimary);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudentFocus $tblStudentFocus
     *
     * @return bool
     */
    public function removeStudentFocus(TblStudentFocus $tblStudentFocus)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentFocus $Entity */
        $Entity = $Manager->getEntityById('TblStudentFocus', $tblStudentFocus->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return false|TblStudentFocus
     */
    public function getStudentFocusPrimary(TblStudent $tblStudent)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentFocus', array(
            TblStudentFocus::ATTR_TBL_STUDENT => $tblStudent->getId(),
            TblStudentFocus::ATTR_IS_PRIMARY => true
        ));
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentDisorderType $tblStudentDisorderType
     *
     * @return false|TblStudentDisorder
     */
    public function getStudentDisorder(TblStudent $tblStudent, TblStudentDisorderType $tblStudentDisorderType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentDisorder', array(
            TblStudentDisorder::ATTR_TBL_STUDENT => $tblStudent->getId(),
            TblStudentDisorder::ATTR_TBL_STUDENT_DISORDER_TYPE => $tblStudentDisorderType->getId()
        ));
    }
}
