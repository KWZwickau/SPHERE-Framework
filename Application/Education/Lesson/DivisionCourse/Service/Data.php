<?php
namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use DateTime;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\ColumnHydrator;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Extension;

class Data extends DataTeacher
{
    public function setupDatabaseContent()
    {
        $this->createDivisionCourseType('Klasse', TblDivisionCourseType::TYPE_DIVISION);
        $this->createDivisionCourseType('Stammgruppe', TblDivisionCourseType::TYPE_CORE_GROUP);
        $this->createDivisionCourseType('Unterrichtsgruppe', TblDivisionCourseType::TYPE_TEACHING_GROUP);
        $this->createDivisionCourseType('SekII-Leistungskurs', TblDivisionCourseType::TYPE_ADVANCED_COURSE);
        $this->createDivisionCourseType('SekII-Grundkurs', TblDivisionCourseType::TYPE_BASIC_COURSE);
        $this->createDivisionCourseType('Lerngruppe', TblDivisionCourseType::TYPE_TEACHER_GROUP);

        $this->createDivisionCourseMemberType('Schüler', TblDivisionCourseMemberType::TYPE_STUDENT);
        $this->createDivisionCourseMemberType('Gruppenleiter', TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER);
        // rename
        if (($tblTemp = $this->createDivisionCourseMemberType('Elternsprecher', TblDivisionCourseMemberType::TYPE_CUSTODY))
            && $tblTemp->getName() != 'Elternsprecher'
        ) {
            $this->updateDivisionCourseMemberType($tblTemp, 'Elternsprecher');
        }
        // rename
        if (($tblTemp = $this->createDivisionCourseMemberType('Klassensprecher', TblDivisionCourseMemberType::TYPE_REPRESENTATIVE))
            && $tblTemp->getName() != 'Klassensprecher'
        ) {
            $this->updateDivisionCourseMemberType($tblTemp, 'Klassensprecher');
        }

        /*
         * Stundentafel
         */
        $this->setupDatabaseContentForSubjectTable();

        /**
         * Migration der alten Klassen-Daten in die neue DB-Struktur
         */
//        $this->migrateAll();
    }

    /**
     * @param string $Name
     * @param string $Identifier
     *
     * @return TblDivisionCourseType
     */
    public function createDivisionCourseType(string $Name, string  $Identifier): TblDivisionCourseType
    {
        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblDivisionCourseType')->findOneBy(array(
            TblDivisionCourseType::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblDivisionCourseType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Identifier
     *
     * @return TblDivisionCourseMemberType
     */
    public function createDivisionCourseMemberType(string $Name, string  $Identifier): TblDivisionCourseMemberType
    {
        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblDivisionCourseMemberType')->findOneBy(array(
            TblDivisionCourseMemberType::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblDivisionCourseMemberType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourseMemberType $tblDivisionCourseMemberType
     * @param string $name
     *
     * @return bool
     */
    public function updateDivisionCourseMemberType(TblDivisionCourseMemberType $tblDivisionCourseMemberType, string $name): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblDivisionCourseMemberType $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourseMemberType', $tblDivisionCourseMemberType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param TblSubject|null $tblSubject
     *
     * @return TblDivisionCourse
     */
    public function createDivisionCourse(TblDivisionCourseType $tblType, TblYear $tblYear, string $name, string $description,
        bool $isShownInPersonData, bool $isReporting, ?TblSubject $tblSubject): TblDivisionCourse
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCourse')->findOneBy(array(
            TblDivisionCourse::ATTR_NAME => $name,
            TblDivisionCourse::SERVICE_TBL_YEAR => $tblYear->getId(),
        ));
        if($Entity === null) {
            $Entity = TblDivisionCourse::withParameter($tblType, $tblYear, $name, $description, $isShownInPersonData, $isReporting, $tblSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param TblSubject|null $tblSubject
     *
     * @return bool
     */
    public function updateDivisionCourse(TblDivisionCourse $tblDivisionCourse, string $name, string $description,
        bool $isShownInPersonData, bool $isReporting, ?TblSubject $tblSubject): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblDivisionCourse $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourse', $tblDivisionCourse->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($name);
            $Entity->setDescription($description);
            $Entity->setIsShownInPersonData($isShownInPersonData);
            $Entity->setIsReporting($isReporting);
            $Entity->setServiceTblSubject($tblSubject);
//            $Entity->setIsUcs($isUcs);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function destroyDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDivisionCourse $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourse', $tblDivisionCourse->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByMigrateGroupId($Id)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', array(TblDivisionCourse::ATTR_MIGRATE_GROUP_ID => $Id));
    }

    /**
     * @param $string
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByMigrateSekCourse($string)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', array(TblDivisionCourse::ATTR_MIGRATE_SEK_COURSE => $string));
    }

    /**
     * @param string|null $TypeIdentifier
     * @param bool $isReporting
     * @param bool $isShowInPersonData
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseAll(?string $TypeIdentifier = '', $isReporting = false, $isShowInPersonData = false)
    {

        $Parameter = array();
        if ($TypeIdentifier && ($tblType = $this->getDivisionCourseTypeByIdentifier($TypeIdentifier))) {
            $Parameter[TblDivisionCourse::ATTR_TBL_TYPE] = $tblType->getId();
        }
        if($isReporting){
            $Parameter[TblDivisionCourse::ATTR_IS_REPORTING] = 1;
        }
        if($isShowInPersonData){
            $Parameter[TblDivisionCourse::ATTR_IS_SHOWN_IN_PERSON_DATA] = 1;
        }

        if(!empty($Parameter)){
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', $Parameter);
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse');
        }
    }

    /**
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListBy(TblYear $tblYear = null, ?string $TypeIdentifier = '')
    {
        $parameterList = array();
        if ($TypeIdentifier && ($tblType = $this->getDivisionCourseTypeByIdentifier($TypeIdentifier))) {
            $parameterList[TblDivisionCourse::ATTR_TBL_TYPE] = $tblType->getId();
        }
        if ($tblYear) {
            $parameterList[TblDivisionCourse::SERVICE_TBL_YEAR] = $tblYear->getId();
        }

        if ($parameterList) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', $parameterList);
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse');
        }
    }

    /**
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByIsShownInPersonData(TblYear $tblYear = null, ?string $TypeIdentifier = '')
    {
        $parameterList[TblDivisionCourse::ATTR_IS_SHOWN_IN_PERSON_DATA] = 1;
        if ($TypeIdentifier && ($tblType = $this->getDivisionCourseTypeByIdentifier($TypeIdentifier))) {
            $parameterList[TblDivisionCourse::ATTR_TBL_TYPE] = $tblType->getId();
        }
        if ($tblYear) {
            $parameterList[TblDivisionCourse::SERVICE_TBL_YEAR] = $tblYear->getId();
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', $parameterList, array(TblDivisionCourse::ATTR_NAME => self::ORDER_ASC));
    }

    /**
     * @param string $name
     * @param array|null $tblYearList
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByLikeName(string $name, ?array $tblYearList = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($tblYearList) {
            $count = 2;
            $or = $queryBuilder->expr()->orX();
            foreach ($tblYearList as $tblYear) {
                $or->add($queryBuilder->expr()->eq('t.serviceTblYear', '?' . $count));
                $queryBuilder->setParameter($count, $tblYear->getId());
                $count++;
            }
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblDivisionCourse', 't')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->like('t.Name', '?1'),
                        $or
                    )
                )
                ->setParameter(1, '%' . $name . '%')
                ->getQuery();
        } else {
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblDivisionCourse', 't')
                ->where(
                    $queryBuilder->expr()->like('t.Name', '?1')
                )
                ->setParameter(1, '%' . $name . '%')
                ->getQuery();
        }

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblYear $tblYear
     * @param $isReporting
     * @param $isShowInPersonData
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByYear(TblYear $tblYear, $isReporting, $isShowInPersonData)
    {

        $Parameter[TblDivisionCourse::SERVICE_TBL_YEAR] = $tblYear->getId();
        if($isReporting){
            $Parameter[TblDivisionCourse::ATTR_IS_REPORTING] = 1;
        }
        if($isShowInPersonData){
            $Parameter[TblDivisionCourse::ATTR_IS_SHOWN_IN_PERSON_DATA] = 1;
        }
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse',$Parameter);
    }

    /**
     * @param $name
     * @param TblYear $tblYear
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseByNameAndYear($name, TblYear $tblYear)
    {
        return $this->getForceEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', array(
            TblDivisionCourse::ATTR_NAME => $name,
            TblDivisionCourse::SERVICE_TBL_YEAR => $tblYear->getId()
        ));
    }

    /**
     * @param TblYear $tblYear
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblType|null $tblTypeSchool
     * @param string $level
     *
     * @return array
     */
    public function getDivisionCourseListByYearAndDivisionCourseAndTypeAndLevel(TblYear $tblYear, ?TblDivisionCourse $tblDivisionCourse = null,
        ?TblType $tblTypeSchool = null, string $level = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblEntityStudentEducation = new TblStudentEducation();

        $query = $queryBuilder->select('tSE.serviceTblPerson as PersonId');
        $query->from($tblEntityStudentEducation->getEntityFullName(), 'tSE');
        $query->where($queryBuilder->expr()->eq('tSE.serviceTblYear', '?1'));
        $query->setParameter(1, $tblYear->getId());
        if($tblDivisionCourse){
            $query->andWhere($queryBuilder->expr()->orX($queryBuilder->expr()->eq('tSE.tblDivision', '?2'),
                $queryBuilder->expr()->eq('tSE.tblCoreGroup', '?3')));
            $query->setParameter(2, $tblDivisionCourse->getId());
            $query->setParameter(3, $tblDivisionCourse->getId());
        }
        if($tblTypeSchool){
            $query->andWhere($queryBuilder->expr()->eq('tSE.serviceTblSchoolType', '?4'));
            $query->setParameter(4, $tblTypeSchool->getId());
        }
        if($level){
            $query->andWhere($queryBuilder->expr()->eq('tSE.Level', '?5'));
            $query->setParameter(5, $level);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $FilterList
     *
     * @return array|float|int|string
     */
    public function fetchIdPersonByFilter(array $FilterList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $EducationStudent = new TblStudentEducation();
        $DivisionCourse = new TblDivisionCourse();
        $query = $queryBuilder->select('tES.serviceTblPerson as PersonId, tES.serviceTblYear as YearId, tES.Level, tDC.Name as DivisionName')
            ->from($EducationStudent->getEntityFullName(), 'tES')
            ->leftJoin($DivisionCourse->getEntityFullName(), 'tDC', 'WITH', 'tDC.Id = tES.tblDivision')
            ->where($queryBuilder->expr()->isNull('tES.EntityRemove'));
        $ParameterCount = 1;
        foreach($FilterList as $FilterName => $FilterValue){
            if($FilterValue != null){
                if($FilterName == 'TblYear_Id'){
                    $query->andWhere($queryBuilder->expr()->eq('tES.serviceTblYear', '?'.$ParameterCount))
                    ->setParameter($ParameterCount++, $FilterValue);
                }
                if($FilterName == 'TblSchoolType_Id' && $FilterValue != 0){
                    $query->andWhere($queryBuilder->expr()->eq('tES.serviceTblSchoolType', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
                if($FilterName == 'Level' && $FilterValue != 0){
                    $query->andWhere($queryBuilder->expr()->eq('tES.Level', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
                if($FilterName == 'TblDivisionCourse_Name'){
                    $query->andWhere($queryBuilder->expr()->eq('tDC.Name', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
            }
        }
        $query = $query->getQuery();
        return $query->getResult();
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return TblDivisionCourseLink
     */
    public function addSubDivisionCourseToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCourseLink')
            ->findOneBy(array(
                TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDivisionCourseLink::ATTR_TBL_SUB_DIVISION_COURSE => $tblSubDivisionCourse->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblDivisionCourseLink();
            $Entity->setTblDivisionCourse($tblDivisionCourse);
            $Entity->setTblSubDivisionCourse($tblSubDivisionCourse);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return bool
     */
    public function removeSubDivisionCourseFromDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblDivisionCourseLink')
            ->findBy(array(
                TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDivisionCourseLink::ATTR_TBL_SUB_DIVISION_COURSE => $tblSubDivisionCourse->getId()
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                $Manager->killEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourseLink $tblDivisionCourseLink
     *
     * @return bool
     */
    public function destroyDivisionCourseLink(TblDivisionCourseLink $tblDivisionCourseLink): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblDivisionCourseLink', $tblDivisionCourseLink->getId());
        if(null !== $Entity){
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getSubDivisionCourseListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $resultList = array();
        if (($list = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseLink',
            array(TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId())))
        ) {
            /** @var TblDivisionCourseLink $tblDivisionCourseLink */
            foreach ($list as $tblDivisionCourseLink) {
                if (($tblSubDivisionCourse = $tblDivisionCourseLink->getTblSubDivisionCourse())) {
                    $resultList[] = $tblSubDivisionCourse;
                }
            }
        }

        if ($resultList) {
            return (new Extension())->getSorter($resultList)->sortObjectBy('Name');
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblDivisionCourseLink[]
     */
    public function getDivisionCourseLinkListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseLink',
            array(TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId()));
    }

    /**
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getAboveDivisionCourseListBySubDivisionCourse(TblDivisionCourse $tblSubDivisionCourse)
    {
        $resultList = array();
        if (($list = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseLink',
            array(TblDivisionCourseLink::ATTR_TBL_SUB_DIVISION_COURSE => $tblSubDivisionCourse->getId())))
        ) {
            /** @var TblDivisionCourseLink $tblDivisionCourseLink */
            foreach ($list as $tblDivisionCourseLink) {
                if (($tblDivisionCourse = $tblDivisionCourseLink->getTblDivisionCourse())) {
                    $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                }
            }
        }

        if ($resultList) {
            return (new Extension())->getSorter($resultList)->sortObjectBy('Name');
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseLink
     */
    public function getDivisionCourseLinkById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseLink', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeByIdentifier(string $Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType',
            array(TblDivisionCourseType::ATTR_IDENTIFIER => strtoupper($Identifier)));
    }

    /**
     * @return false|TblDivisionCourseType[]
     */
    public function getDivisionCourseTypeAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType');
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMemberType', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeByIdentifier(string $Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMemberType',
            array(TblDivisionCourseMemberType::ATTR_IDENTIFIER => strtoupper($Identifier)));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourseMember[]|false
     */
    public function getDivisionCourseMemberStudentByDivision(TblDivisionCourse $tblDivisionCourse)
    {
        $tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
                TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId()
            ));

        $resultList = array();
        if ($tempList
            && ($tblDivisionCourseMemberType = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))
        ) {
            /** @var TblStudentEducation $tblStudentEducation */
            foreach ($tempList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    $resultList[] = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson, '',
                        $tblStudentEducation->getLeaveDateTime(), $tblStudentEducation->getDivisionSortOrder());
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourseMember[]|false
     */
    public function getDivisionCourseMemberStudentByCoreGroup(TblDivisionCourse $tblDivisionCourse)
    {
        $tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId()
        ));

        $resultList = array();
        if ($tempList
            && ($tblDivisionCourseMemberType = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))
        ) {
            /** @var TblStudentEducation $tblStudentEducation */
            foreach ($tempList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    $resultList[] = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson, '',
                        $tblStudentEducation->getLeaveDateTime(), $tblStudentEducation->getCoreGroupSortOrder());
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param DateTime|null $leaveDate
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByDivision(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?DateTime $leaveDate)
    {
        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => $leaveDate
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByDivision(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param DateTime|null $leaveDate
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByCoreGroup(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?DateTime $leaveDate)
    {
        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => $leaveDate
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByCoreGroup(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsForced
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByPerson(TblPerson $tblPerson, bool $IsForced = false)
    {
        if ($IsForced) {
            return $this->getForceEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
                TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        } else {
            return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
                TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear   $tblYear
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
        ));
    }

    /**
     * @return mixed
     */
    public function getStudentEducationLevelList()
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $tblEntityStudentEducation = new TblStudentEducation();
        $query = $queryBuilder->select('tSE.Level as Level');
        $query->from($tblEntityStudentEducation->getEntityFullName(), 'tSE');
        $query->groupBy('tSE.Level');
        return $query->getQuery()->getResult(ColumnHydrator::HYDRATION_MODE);
    }

    /**
     * @param TblYear $tblYear
     * @param TblType|null $tblSchoolType
     * @param null $level
     * @param TblDivisionCourse|null $tblDivision
     * @param TblDivisionCourse|null $tblCoreGroup
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListBy(TblYear $tblYear, TblType $tblSchoolType = null, $level = null, TblDivisionCourse $tblDivision = null,
        TblDivisionCourse $tblCoreGroup = null)
    {
        $parameters[TblStudentEducation::ATTR_SERVICE_TBL_YEAR] = $tblYear->getId();
        $parameters[TblStudentEducation::ATTR_LEAVE_DATE] = null;
        if ($tblSchoolType) {
            $parameters[TblStudentEducation::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();
        }
        if ($level) {
            $parameters[TblStudentEducation::ATTR_LEVEL] = $level;
        }
        if ($tblDivision) {
            $parameters[TblStudentEducation::ATTR_TBL_DIVISION] = $tblDivision->getId();
        }
        if ($tblCoreGroup) {
            $parameters[TblStudentEducation::ATTR_TBL_CORE_GROUP] = $tblCoreGroup->getId();
        }

        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', $parameters);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => null
        ));
    }

    /**
     * @param $Id
     *
     * @return TblStudentEducation|false
     */
    public function getStudentEducationById($Id)
    {
        return $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', $Id);
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return TblStudentEducation
     */
    public function createStudentEducation(TblStudentEducation $tblStudentEducation): TblStudentEducation
    {
        $Manager = $this->getEntityManager();

        $Manager->saveEntity($tblStudentEducation);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblStudentEducation);

        return $tblStudentEducation;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return bool
     */
    public function updateStudentEducation(TblStudentEducation $tblStudentEducation): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblStudentEducation $Protocol */
        $Protocol = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
        if (null !== $Protocol) {
            $Manager->saveEntity($tblStudentEducation);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $tblStudentEducation);

            return true;
        }

        return false;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return bool
     */
    public function destroyStudentEducation(TblStudentEducation $tblStudentEducation): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudentEducation $Entity */
        $Entity = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param TblDivisionCourse|null $tblDivision
     * @param $divisionSortOrder
     * @param TblDivisionCourse|null $tblCoreGroup
     * @param $coreGroupSortOrder
     * @param DateTime|null $leaveDate
     *
     * @return bool
     */
    public function updateStudentEducationByProperties(TblStudentEducation $tblStudentEducation, ?TblDivisionCourse $tblDivision, $divisionSortOrder,
        ?TblDivisionCourse $tblCoreGroup, $coreGroupSortOrder, ?DateTime $leaveDate): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblStudentEducation $Entity */
        $Entity = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblDivision($tblDivision);
            $Entity->setDivisionSortOrder($divisionSortOrder);
            $Entity->setTblCoreGroup($tblCoreGroup);
            $Entity->setCoreGroupSortOrder($coreGroupSortOrder);
            $Entity->setLeaveDate($leaveDate);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * zählt die aktiven Schüler einer Klasse
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByDivision(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => null
        )))) {
            return count($tempList);
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler einer Klasse
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByDivision(TblDivisionCourse $tblDivisionCourse): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblDivision', '?1'),
                    $queryBuilder->expr()->isNotNull('t.LeaveDate')
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return count($resultList);

        // funktioniert leider nicht
//        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
//            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
//            TblStudentEducation::ATTR_LEAVE_DATE => !null
//        )))) {
//            return count($tempList);
//        }
//
//        return 0;
    }

    /**
     * zählt die aktiven Schüler einer Stammgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByCoreGroup(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => null
        )))) {
            return count($tempList);
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler einer Stammgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByCoreGroup(TblDivisionCourse $tblDivisionCourse): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblCoreGroup', '?1'),
                    $queryBuilder->expr()->isNotNull('t.LeaveDate')
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return count($resultList);
    }

    /**
     * zählt die aktiven Schüler einer Unterrichtsgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivisionCourseMember', array(
            TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT),
            TblDivisionCourseMember::ATTR_LEAVE_DATE => null
        )))) {
            return count($tempList);
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler einer Unterrichtsgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourse', '?1'),
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourseMemberType', '?2'),
                    $queryBuilder->expr()->isNotNull('t.LeaveDate')
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT)->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return count($resultList);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMember', $Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return TblDivisionCourseMember[]|false
     */
    public function getDivisionCourseMemberListBy(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMember', array(
            TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $tblMemberType->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberByPerson(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType, TblPerson $tblPerson)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMember', array(
            TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $tblMemberType->getId(),
            TblDivisionCourseMember::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     * @param string $description
     * @param int|null $sortOrder
     * @return TblDivisionCourseMember
     */
    public function addDivisionCourseMemberToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType,
        TblPerson $tblPerson, string $description = '', ?int $sortOrder = null): TblDivisionCourseMember
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCourseMember')
            ->findOneBy(array(
                TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $tblMemberType->getId(),
                TblDivisionCourseMember::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));

        if (null === $Entity) {
            $Entity = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblMemberType, $tblPerson, $description, null, $sortOrder);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourseMember $tblDivisionCourseMember
     *
     * @return bool
     */
    public function removeDivisionCourseMemberFromDivisionCourse(TblDivisionCourseMember $tblDivisionCourseMember): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDivisionCourse $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourseMember', $tblDivisionCourseMember->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function removeDivisionCourseMemberAllFromDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblDivisionCourseMember')
            ->findBy(array(
                TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                $Manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();

            return true;
        }

        return false;
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function createDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
            $Manager->bulkSaveEntity($tblDivisionCourseMember);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblDivisionCourseMember, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function updateDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
            $Manager->bulkSaveEntity($tblDivisionCourseMember);
            /** @var TblDivisionCourseMember $Entity */
            $Entity = $Manager->getEntityById('TblDivisionCourseMember', $tblDivisionCourseMember->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblDivisionCourseMember, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function removeDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblDivisionCourseMemberList as $Entity) {
            $Manager->bulkKillEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return int|null
     */
    public function getDivisionCourseMemberMaxSortOrder(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType): ?int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('Max(t.SortOrder) as MaxSortOrder')
            ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourse', '?1'),
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourseMemberType', '?2'),
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $tblMemberType->getId())
            ->orderBy('MaxSortOrder', 'DESC')
            ->getQuery();

        $result = $query->getResult();

        return is_array($result) ? (current($result))['MaxSortOrder'] : null;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return false|TblDivisionCourseMember[]
     */
    public function getDivisionCourseMemberListByPersonAndYearAndMemberType(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourseMemberType $tblMemberType)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('m')
            ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
            ->join(__NAMESPACE__ . '\Entity\TblDivisionCourse', 'c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', 'c.Id'),
                    $queryBuilder->expr()->eq('m.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?2'),
                    $queryBuilder->expr()->eq('c.serviceTblYear', '?3'),
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblMemberType->getId())
            ->setParameter(3, $tblYear->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * Lerngruppen eines Lehrers
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblDivisionCourse[]
     */
    public function getTeacherGroupListByTeacherAndYear(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($tblSubject) {
            $query = $queryBuilder->select('c')
                ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
                ->join(__NAMESPACE__ . '\Entity\TblDivisionCourse', 'c')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', 'c.Id'),
                        $queryBuilder->expr()->eq('m.serviceTblPerson', '?1'),
                        $queryBuilder->expr()->eq('c.serviceTblYear', '?2'),
                        $queryBuilder->expr()->eq('c.tblLessonDivisionCourseType', '?3'),
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?4'),
                        $queryBuilder->expr()->eq('c.serviceTblSubject', '?5')
                    ),
                )
                ->setParameter(1, $tblPerson->getId())
                ->setParameter(2, $tblYear->getId())
                ->setParameter(3, ($this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP))->getId())
                ->setParameter(4, ($this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))->getId())
                ->setParameter(5, $tblSubject->getId())
                ->getQuery();
        } else {
            $query = $queryBuilder->select('c')
                ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
                ->join(__NAMESPACE__ . '\Entity\TblDivisionCourse', 'c')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', 'c.Id'),
                        $queryBuilder->expr()->eq('m.serviceTblPerson', '?1'),
                        $queryBuilder->expr()->eq('c.serviceTblYear', '?2'),
                        $queryBuilder->expr()->eq('c.tblLessonDivisionCourseType', '?3'),
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?4'),
                    ),
                )
                ->setParameter(1, $tblPerson->getId())
                ->setParameter(2, $tblYear->getId())
                ->setParameter(3, ($this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP))->getId())
                ->setParameter(4, ($this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))->getId())
                ->getQuery();
        }

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblDivisionCourse[]
     */
    public function getTeacherGroupListByStudentAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('c')
            ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
            ->join(__NAMESPACE__ . '\Entity\TblDivisionCourse', 'c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', 'c.Id'),
                    $queryBuilder->expr()->eq('m.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('c.serviceTblYear', '?2'),
                    $queryBuilder->expr()->eq('c.serviceTblSubject', '?3'),
                    $queryBuilder->expr()->eq('c.tblLessonDivisionCourseType', '?4'),
                    $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?5'),
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblYear->getId())
            ->setParameter(3, $tblSubject->getId())
            ->setParameter(4, ($this->getDivisionCourseTypeByIdentifier(TblDivisionCourseType::TYPE_TEACHER_GROUP))->getId())
            ->setParameter(5, ($this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivision
     *
     * @return int|null
     */
    public function getStudentEducationDivisionMaxSortOrder(TblDivisionCourse $tblDivision): ?int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('Max(t.DivisionSortOrder) as MaxSortOrder')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblDivision', '?1'),
                )
            )
            ->setParameter(1, $tblDivision->getId())
            ->orderBy('MaxSortOrder', 'DESC')
            ->getQuery();

        $result = $query->getResult();

        return is_array($result) ? (current($result))['MaxSortOrder'] : null;
    }

    /**
     * @param TblDivisionCourse $tblCoreGroup
     *
     * @return int|null
     */
    public function getStudentEducationCoreGroupMaxSortOrder(TblDivisionCourse $tblCoreGroup): ?int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('Max(t.CoreGroupSortOrder) as MaxSortOrder')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblCoreGroup', '?1'),
                )
            )
            ->setParameter(1, $tblCoreGroup->getId())
            ->orderBy('MaxSortOrder', 'DESC')
            ->getQuery();

        $result = $query->getResult();

        return is_array($result) ? (current($result))['MaxSortOrder'] : null;
    }

    /**
     * @param array $tblStudentEducationList
     *
     * @return bool
     */
    public function updateStudentEducationBulk(array $tblStudentEducationList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblStudentEducationList as $tblStudentEducation) {
            $Manager->bulkSaveEntity($tblStudentEducation);
            /** @var TblStudentEducation $Entity */
            $Entity = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblStudentEducation, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getSchoolTypeIdListByTypeDivision(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('e.serviceTblSchoolType as SchoolTypeId')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('e.tblDivision', '?1'),
                    $queryBuilder->expr()->isNull('e.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getSchoolTypeIdListByTypeCoreGroup(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('e.serviceTblSchoolType as SchoolTypeId')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('e.tblCoreGroup', '?1'),
                    $queryBuilder->expr()->isNull('e.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getSchoolTypeIdListByDivisionCourseWithMember(TblDivisionCourse $tblDivisionCourse)
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $Manager = $this->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('e.serviceTblSchoolType as SchoolTypeId')
                ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
                ->join(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', '?1'),
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?2'),
                        $queryBuilder->expr()->isNull('e.EntityRemove'),
                        $queryBuilder->expr()->eq('e.serviceTblYear', '?3'),
                        $queryBuilder->expr()->eq('e.serviceTblPerson', 'm.serviceTblPerson'),
                    ),
                )
                ->setParameter(1, $tblDivisionCourse->getId())
                ->setParameter(2, ($this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))->getId())
                ->setParameter(3, $tblYear->getId())
                ->distinct()
                ->getQuery();

            $resultList = $query->getResult();

            return empty($resultList) ? false : $resultList;
        }

        return false;
    }

    /**
     * SEKII
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getSchoolTypeIdListByStudentSubject(TblDivisionCourse $tblDivisionCourse)
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $Manager = $this->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('e.serviceTblSchoolType as SchoolTypeId')
                ->from(TblStudentEducation::class, 'e')
                ->join(TblStudentSubject::class, 'm')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', '?1'),
                        $queryBuilder->expr()->isNull('e.EntityRemove'),
                        $queryBuilder->expr()->eq('e.serviceTblYear', '?2'),
                        $queryBuilder->expr()->eq('e.serviceTblPerson', 'm.serviceTblPerson'),
                    ),
                )
                ->setParameter(1, $tblDivisionCourse->getId())
                ->setParameter(2, $tblYear->getId())
                ->distinct()
                ->getQuery();

            $resultList = $query->getResult();

            return empty($resultList) ? false : $resultList;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getLevelListByTypeDivision(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('e.Level as Level')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('e.tblDivision', '?1'),
                    $queryBuilder->expr()->isNull('e.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getLevelListByTypeCoreGroup(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('e.Level as Level')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('e.tblCoreGroup', '?1'),
                    $queryBuilder->expr()->isNull('e.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getLevelListByDivisionCourseWithMember(TblDivisionCourse $tblDivisionCourse)
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $Manager = $this->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('e.Level as Level')
                ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
                ->join(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', '?1'),
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?2'),
                        $queryBuilder->expr()->isNull('e.EntityRemove'),
                        $queryBuilder->expr()->eq('e.serviceTblYear', '?3'),
                        $queryBuilder->expr()->eq('e.serviceTblPerson', 'm.serviceTblPerson'),
                    ),
                )
                ->setParameter(1, $tblDivisionCourse->getId())
                ->setParameter(2, ($this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))->getId())
                ->setParameter(3, $tblYear->getId())
                ->distinct()
                ->getQuery();

            $resultList = $query->getResult();

            return empty($resultList) ? false : $resultList;
        }

        return false;
    }

    /**
     * SEKII
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getLevelListByStudentSubject(TblDivisionCourse $tblDivisionCourse)
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $Manager = $this->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('e.Level as Level')
                ->from(TblStudentEducation::class, 'e')
                ->join(TblStudentSubject::class, 'm')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', '?1'),
                        $queryBuilder->expr()->isNull('e.EntityRemove'),
                        $queryBuilder->expr()->eq('e.serviceTblYear', '?2'),
                        $queryBuilder->expr()->eq('e.serviceTblPerson', 'm.serviceTblPerson'),
                    ),
                )
                ->setParameter(1, $tblDivisionCourse->getId())
                ->setParameter(2, $tblYear->getId())
                ->distinct()
                ->getQuery();

            $resultList = $query->getResult();

            return empty($resultList) ? false : $resultList;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getCompanyIdListByTypeDivision(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('e.serviceTblCompany as CompanyId')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('e.tblDivision', '?1'),
                    $queryBuilder->expr()->isNull('e.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getCompanyIdListByTypeCoreGroup(TblDivisionCourse $tblDivisionCourse)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('e.serviceTblCompany as CompanyId')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('e.tblCoreGroup', '?1'),
                    $queryBuilder->expr()->isNull('e.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getCompanyIdListByDivisionCourseWithMember(TblDivisionCourse $tblDivisionCourse)
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $Manager = $this->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('e.serviceTblCompany as CompanyId')
                ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 'e')
                ->join(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 'm')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', '?1'),
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourseMemberType', '?2'),
                        $queryBuilder->expr()->isNull('e.EntityRemove'),
                        $queryBuilder->expr()->eq('e.serviceTblYear', '?3'),
                        $queryBuilder->expr()->eq('e.serviceTblPerson', 'm.serviceTblPerson'),
                    ),
                )
                ->setParameter(1, $tblDivisionCourse->getId())
                ->setParameter(2, ($this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))->getId())
                ->setParameter(3, $tblYear->getId())
                ->distinct()
                ->getQuery();

            $resultList = $query->getResult();

            return empty($resultList) ? false : $resultList;
        }

        return false;
    }

    /**
     * SEKII
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array|false
     */
    public function getCompanyIdListByStudentSubject(TblDivisionCourse $tblDivisionCourse)
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $Manager = $this->getEntityManager();
            $queryBuilder = $Manager->getQueryBuilder();

            $query = $queryBuilder->select('e.serviceTblCompany as CompanyId')
                ->from(TblStudentEducation::class, 'e')
                ->join(TblStudentSubject::class, 'm')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('m.tblLessonDivisionCourse', '?1'),
                        $queryBuilder->expr()->isNull('e.EntityRemove'),
                        $queryBuilder->expr()->eq('e.serviceTblYear', '?2'),
                        $queryBuilder->expr()->eq('e.serviceTblPerson', 'm.serviceTblPerson'),
                    ),
                )
                ->setParameter(1, $tblDivisionCourse->getId())
                ->setParameter(2, $tblYear->getId())
                ->distinct()
                ->getQuery();

            $resultList = $query->getResult();

            return empty($resultList) ? false : $resultList;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function getIsCourseSystemByStudentsInDivisionOrCoreGroup(TblDivisionCourse $tblDivisionCourse): bool
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq('t.tblDivision', '?1'),
                        $queryBuilder->expr()->eq('t.tblCoreGroup', '?1'),
                    ),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('t.serviceTblSchoolType', '?2'),
                            $queryBuilder->expr()->orX(
                                $queryBuilder->expr()->eq('t.Level', '?4'),
                                $queryBuilder->expr()->eq('t.Level', '?5'),
                            )
                        ),
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('t.serviceTblSchoolType', '?3'),
                            $queryBuilder->expr()->orX(
                                $queryBuilder->expr()->eq('t.Level', '?5'),
                                $queryBuilder->expr()->eq('t.Level', '?6'),
                            )
                        ),
                    ),
                    $queryBuilder->expr()->isNull('t.LeaveDate'),
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, ($tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy')) ? $tblSchoolTypeGy->getId() : -1)
            ->setParameter(3, ($tblSchoolTypeBgy = Type::useService()->getTypeByShortName('BGy')) ? $tblSchoolTypeBgy->getId() : -1)
            ->setParameter(4, 11)
            ->setParameter(5, 12)
            ->setParameter(6, 13)
            ->getQuery();


        $resultList = $query->getResult();

        return !empty($resultList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblEntityList as $tblEntity) {
            $Manager->bulkSaveEntity($tblEntity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblEntity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {
            $Manager->bulkSaveEntity($tblElement);
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblElement, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {

            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());

            $Manager->bulkKillEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removePerson(TblPerson $tblPerson, bool $IsSoftRemove)
    {
        $Manager = $this->getEntityManager();

        if (($tblStudentEducationList = $this->getStudentEducationListByPerson($tblPerson))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblStudentEducation);

                if ($IsSoftRemove) {
                    $Manager->removeEntity($tblStudentEducation);
                } else {
                    $Manager->killEntity($tblStudentEducation);
                }
            }
        }

        if (($tblDivisionCourseMemberList = $this->getCachedEntityListBy(
            __METHOD__, $Manager, 'TblDivisionCourseMember', array(TblDivisionCourseMember::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId())
        ))) {
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblDivisionCourseMember);

                if ($IsSoftRemove) {
                    $Manager->removeEntity($tblDivisionCourseMember);
                } else {
                    $Manager->killEntity($tblDivisionCourseMember);
                }
            }
        }

        if (($tblStudentSubjectList = $this->getCachedEntityListBy(
            __METHOD__, $Manager, 'TblStudentSubject', array(TblStudentSubject::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId())
        ))) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblStudentSubject);

                if ($IsSoftRemove) {
                    $Manager->removeEntity($tblStudentSubject);
                } else {
                    $Manager->killEntity($tblStudentSubject);
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function restorePerson(TblPerson $tblPerson)
    {
        $Manager = $this->getEntityManager();

        if (($tblStudentEducationList = $this->getStudentEducationListByPerson($tblPerson, true))) {
            foreach ($tblStudentEducationList as $tblStudentEducation) {
                $Protocol = clone $tblStudentEducation;
                $tblStudentEducation->setEntityRemove(null);
                $Manager->saveEntity($tblStudentEducation);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $tblStudentEducation);
            }
        }

        if (($tblDivisionCourseMemberList = $this->getForceEntityListBy(
            __METHOD__, $Manager, 'TblDivisionCourseMember', array(TblDivisionCourseMember::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId())
        ))) {
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                $Protocol = clone $tblDivisionCourseMember;
                $tblDivisionCourseMember->setEntityRemove(null);
                $Manager->saveEntity($tblDivisionCourseMember);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $tblDivisionCourseMember);
            }
        }

        if (($tblStudentSubjectList = $this->getForceEntityListBy(
            __METHOD__, $Manager, 'TblStudentSubject', array(TblStudentSubject::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId())
        ))) {
            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                $Protocol = clone $tblStudentSubject;
                $tblStudentSubject->setEntityRemove(null);
                $Manager->saveEntity($tblStudentSubject);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $tblStudentSubject);
            }
        }
    }
}