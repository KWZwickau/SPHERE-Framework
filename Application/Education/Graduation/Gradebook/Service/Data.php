<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:31
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param             $Name
     * @param             $Code
     * @param             $Description
     * @param             $IsHighlighted
     * @param TblTestType $tblTestType
     *
     * @return null|TblGradeType
     */
    public function createGradeType($Name, $Code, $Description, $IsHighlighted, TblTestType $tblTestType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblGradeType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCode($Code);
            $Entity->setHighlighted($IsHighlighted);
            $Entity->setServiceTblTestType($tblTestType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param              $Name
     * @param              $Code
     * @param              $Description
     * @param              $IsHighlighted
     *
     * @return bool
     */
    public function updateGradeType(
        TblGradeType $tblGradeType,
        $Name,
        $Code,
        $Description,
        $IsHighlighted
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblGradeType $Entity */
        $Entity = $Manager->getEntityById('TblGradeType', $tblGradeType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setCode($Code);
            $Entity->setDescription($Description);
            $Entity->setHighlighted($IsHighlighted);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson                                                                      $tblPerson
     * @param TblDivision                                                                    $tblDivision
     * @param TblSubject                                                                     $tblSubject
     * @param TblSubjectGroup|null                                                           $tblSubjectGroup
     * @param TblPeriod|null                                                                 $tblPeriod
     * @param TblGradeType|null                                                              $tblGradeType
     * @param TblTest                                                                        $tblTest
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType $tblTestType
     * @param                                                                                $Grade
     * @param string                                                                         $Comment
     *
     * @return TblGrade
     */
    public function createGrade(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblSubjectGroup $tblSubjectGroup = null,
        TblPeriod $tblPeriod = null,
        TblGradeType $tblGradeType = null,
        TblTest $tblTest,
        TblTestType $tblTestType,
        $Grade,
        $Comment
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblGrade();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblSubjectGroup($tblSubjectGroup);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setTblGradeType($tblGradeType);
        $Entity->setServiceTblTest($tblTest);
        $Entity->setServiceTblTestType($tblTestType);
        $Entity->setGrade($Grade);
        $Entity->setComment($Comment);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblGrade $tblGrade
     *
     * @return bool
     */
    public function destroyGrade(TblGrade $tblGrade)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGrade $Entity */
        $Entity = $Manager->getEntityById('TblGrade', $tblGrade->getId());
        if (null !== $Entity) {

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }


    /**
     * @param $Id
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGradeType', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAllWhereTest()
    {

        $tblTestType = Evaluation::useService()->getTestTypeByIdentifier('TEST');

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType',
            array(
                TblGradeType::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId()
            ));
    }

    /**
     * @param TblPerson            $tblPerson
     * @param TblDivision          $tblDivision
     * @param TblSubject           $tblSubject
     * @param TblTestType          $tblTestType
     * @param TblPeriod|null       $tblPeriod
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return bool|TblGrade[]
     */
    public function getGradesByStudent(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblTestType $tblTestType,
        TblPeriod $tblPeriod = null,
        TblSubjectGroup $tblSubjectGroup = null
    ) {

        if ($tblSubjectGroup === null) {
            if ($tblPeriod === null) {
                return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON    => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION  => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT   => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                    )
                );
            } else {
                return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON    => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION  => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT   => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE => $tblTestType->getId(),
                        TblGrade::ATTR_SERVICE_TBL_PERIOD    => $tblPeriod->getId()
                    )
                );
            }
        } else {
            if ($tblPeriod === null) {
                return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON        => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION      => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT       => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE     => $tblTestType->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId()
                    )
                );
            } else {
                return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
                    array(
                        TblGrade::ATTR_SERVICE_TBL_PERSON        => $tblPerson->getId(),
                        TblGrade::ATTR_SERVICE_TBL_DIVISION      => $tblDivision->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT       => $tblSubject->getId(),
                        TblGrade::ATTR_SERVICE_TBL_TEST_TYPE     => $tblTestType->getId(),
                        TblGrade::ATTR_SERVICE_TBL_PERIOD        => $tblPeriod->getId(),
                        TblGrade::ATTR_SERVICE_TBL_SUBJECT_GROUP => $tblSubjectGroup->getId(),
                    )
                );
            }
        }
    }

    /**
     * @param TblGrade $tblGrade
     * @param          $Grade
     * @param string   $Comment
     *
     * @return bool
     */
    public function updateGrade(
        TblGrade $tblGrade,
        $Grade,
        $Comment = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGrade $Entity */
        $Entity = $Manager->getEntityById('TblGrade', $tblGrade->getId());

        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblGrade
     */
    public function getGradeById($Id)
    {

//        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade', $Id);
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGrade', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblTest   $tblTest
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGrade
     */
    public function getGradeByTestAndStudent(
        TblTest $tblTest,
        TblPerson $tblPerson
    ) {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGrade',
            array(
                TblGrade::ATTR_SERVICE_TBL_TEST   => $tblTest->getId(),
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param \SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest $tblTest
     * @param TblPerson                                                                  $tblPerson
     * @param string                                                                     $Grade
     * @param string                                                                     $Comment
     *
     * @return null|TblGrade
     */
    public function createGradeToTest(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $Grade = '',
        $Comment = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGrade')
            ->findOneBy(array(
                TblGrade::ATTR_SERVICE_TBL_TEST   => $tblTest->getId(),
                TblGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblGrade();
            $Entity->setServiceTblTest($tblTest);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblTest->getServiceTblDivision());
            $Entity->setServiceTblSubject($tblTest->getServiceTblSubject());
            $Entity->setServiceTblPeriod($tblTest->getServiceTblPeriod());
            $Entity->setTblGradeType($tblTest->getServiceTblGradeType());
            $Entity->setServiceTblTestType(Evaluation::useService()->getTestTypeByIdentifier('TEST'));
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     *
     * @return TblGrade[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblGrade')->findBy(array(
            TblGrade::ATTR_SERVICE_TBL_TEST => $tblTest->getId()
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroup
     */
    public function getScoreGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreGroup', $Id);
    }

    /**
     * @return bool|TblScoreGroup[]
     */
    public function getScoreGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreGroup');
    }


    /**
     * @param $Id
     *
     * @return bool|TblScoreCondition
     */
    public function getScoreConditionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreCondition',
            $Id);
    }

    /**
     * @return bool|TblScoreCondition[]
     */
    public function getScoreConditionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreCondition');
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRule
     */
    public function getScoreRuleById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreRule', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblScoreRuleConditionList', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblScoreConditionGradeTypeList', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblScoreConditionGroupList', $Id);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblScoreConditionGroupList',
            array(TblScoreConditionGroupList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId())
        );
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblScoreGroupGradeTypeList', $Id);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblScoreGroupGradeTypeList',
            array(TblScoreGroupGradeTypeList::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId())
        );
    }

    /**
     * @param        $Name
     * @param string $Description
     *
     * @return TblScoreRule
     */
    public function createScoreRule(
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreRule')
            ->findOneBy(array(
                TblScoreRule::ATTR_NAME => $Name,
            ));

        if (null === $Entity) {
            $Entity = new TblScoreRule();
            $Entity->setName($Name);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Name
     * @param $Round
     * @param $Priority
     *
     * @return TblScoreCondition
     */
    public function createScoreCondition(
        $Name,
        $Round,
        $Priority
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreCondition')
            ->findOneBy(array(
                TblScoreCondition::ATTR_NAME => $Name,
            ));

        if (null === $Entity) {
            $Entity = new TblScoreCondition();
            $Entity->setName($Name);
            $Entity->setRound($Round);
            $Entity->setPriority($Priority);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Name
     * @param $Round
     * @param $Multiplier
     *
     * @return TblScoreGroup
     */
    public function createScoreGroup(
        $Name,
        $Round,
        $Multiplier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreGroup')
            ->findOneBy(array(
                TblScoreGroup::ATTR_NAME => $Name,
            ));

        if (null === $Entity) {
            $Entity = new TblScoreGroup();
            $Entity->setName($Name);
            $Entity->setRound($Round);
            $Entity->setMultiplier($Multiplier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }


    /**
     * @param TblScoreRule      $tblScoreRule
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreRuleConditionList
     */
    public function addScoreRuleConditionList(
        TblScoreRule $tblScoreRule,
        TblScoreCondition $tblScoreCondition
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreRuleConditionList')
            ->findOneBy(array(
                TblScoreRuleConditionList::ATTR_TBL_SCORE_RULE      => $tblScoreRule->getId(),
                TblScoreRuleConditionList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreRuleConditionList();
            $Entity->setTblScoreRule($tblScoreRule);
            $Entity->setTblScoreCondition($tblScoreCondition);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGradeType      $tblGradeType
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreConditionGradeTypeList
     */
    public function addScoreConditionGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreCondition $tblScoreCondition
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreConditionGradeTypeList')
            ->findOneBy(array(
                TblScoreConditionGradeTypeList::ATTR_TBL_GRADE_TYPE      => $tblGradeType->getId(),
                TblScoreConditionGradeTypeList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreConditionGradeTypeList();
            $Entity->setTblGradeType($tblGradeType);
            $Entity->setTblScoreCondition($tblScoreCondition);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreGroup     $tblScoreGroup
     *
     * @return TblScoreConditionGroupList
     */
    public function addScoreConditionGroupList(
        TblScoreCondition $tblScoreCondition,
        TblScoreGroup $tblScoreGroup
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreConditionGroupList')
            ->findOneBy(array(
                TblScoreConditionGroupList::ATTR_TBL_SCORE_GROUP     => $tblScoreGroup->getId(),
                TblScoreConditionGroupList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreConditionGroupList();
            $Entity->setTblScoreGroup($tblScoreGroup);
            $Entity->setTblScoreCondition($tblScoreCondition);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGradeType  $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param               $Multiplier
     *
     * @return TblScoreGroupGradeTypeList
     */
    public function addScoreGroupGradeTypeList(
        TblGradeType $tblGradeType,
        TblScoreGroup $tblScoreGroup,
        $Multiplier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblScoreGroupGradeTypeList')
            ->findOneBy(array(
                TblScoreGroupGradeTypeList::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId(),
                TblScoreGroupGradeTypeList::ATTR_TBL_GRADE_TYPE  => $tblGradeType->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreGroupGradeTypeList();
            $Entity->setTblScoreGroup($tblScoreGroup);
            $Entity->setTblGradeType($tblGradeType);
            $Entity->setMultiplier($Multiplier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
     *
     * @return bool
     */
    public function removeScoreGroupGradeTypeList(TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreGroupGradeTypeList $Entity */
        $Entity = $Manager->getEntityById('TblScoreGroupGradeTypeList', $tblScoreGroupGradeTypeList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblScoreConditionGroupList $tblScoreConditionGroupList
     *
     * @return bool
     */
    public function removeScoreConditionGroupList(TblScoreConditionGroupList $tblScoreConditionGroupList)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreConditionGroupList $Entity */
        $Entity = $Manager->getEntityById('TblScoreConditionGroupList', $tblScoreConditionGroupList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

}
