<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:31
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service;

use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeStudentSubjectLink;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {


    }

    /**
     * @param $Name
     * @param $Code
     * @param $Description
     * @param $IsHighlighted
     * @return null|TblGradeType
     */
    public function createGradeType($Name, $Code, $Description, $IsHighlighted)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGradeType')
            ->findOneBy(array(TblGradeType::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblGradeType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setCode($Code);
            $Entity->setIsHighlighted($IsHighlighted);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @param TblGradeType $tblGradeType
     * @param $Grade
     * @param string $Comment
     * @return null|object|TblGradeStudentSubjectLink
     */
    public function createGrade(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod,
        TblGradeType $tblGradeType,
        $Grade,
        $Comment = ''
    ) {
        $Manager = $this->getConnection()->getEntityManager();

//        $Entity = $Manager->getEntity('TblGradeStudentSubjectLink')
//            ->findOneBy(array(
//                TblGradeStudentSubjectLink::ATTR_DATE => $Date,
//                TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_PERIOD => $tblPeriod,
//                TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_PERSON => $tblPerson,
//                TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_SUBJECT => $tblSubject,
//                TblGradeStudentSubjectLink::ATTR_TBL_GRADE_TYPE => $tblGradeType
//            ));

//        if (null === $Entity) {
        $Entity = new TblGradeStudentSubjectLink();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setTblGradeType($tblGradeType);
        $Entity->setGrade($Grade);
        $Entity->setComment($Comment);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
//        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGradeType', $Id);
        return (null === $Entity ? false : $Entity);
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
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblGradeType[]
     */
    public function getGradeTypeAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblGradeType')->findAll();
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @return TblGradeStudentSubjectLink[]|bool
     */
    public function getGradesByStudentAndSubjectAndPeriod(
        TblPerson $tblPerson,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod
    ) {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblGradeStudentSubjectLink')
            ->findBy(array(
                    TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                    TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_PERIOD => $tblPeriod->getId(),
                )
            );

        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblGradeStudentSubjectLink $tblGradeStudentSubjectLink
     * @param $Grade
     * @param string $Comment
     * @return bool
     */
    public function updateGrade(
        TblGradeStudentSubjectLink $tblGradeStudentSubjectLink,
        $Grade,
        $Comment = ''
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGradeStudentSubjectLink $Entity */
        $Entity = $Manager->getEntityById('TblGradeStudentSubjectLink', $tblGradeStudentSubjectLink->getId());
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
     * @return bool|TblGradeStudentSubjectLink
     */
    public function getGradeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblGradeStudentSubjectLink', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param $Id
     * @return bool|TblTest
     */
    public function getTestById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblTest', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblTest[]
     */
    public function getTestAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblTest')->findAll();
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject $tblSubject
     * @param TblPeriod $tblPeriod
     * @param TblGradeType $tblGradeType
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     *
     * @return TblTest
     */
    public function createTest(
        TblDivision $tblDivision,
        TblSubject $tblSubject,
        TblPeriod $tblPeriod,
        TblGradeType $tblGradeType,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblTest();
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblSubject($tblSubject);
        $Entity->setServiceTblPeriod($tblPeriod);
        $Entity->setTblGradeType($tblGradeType);
        $Entity->setDescription($Description);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setCorrectionDate($CorrectionDate ? new \DateTime($CorrectionDate) : null);
        $Entity->setReturnDate($ReturnDate ? new \DateTime($ReturnDate) : null);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @param string $Description
     * @param null $Date
     * @param null $CorrectionDate
     * @param null $ReturnDate
     * @return bool
     */
    public function updateTest(
        TblTest $tblTest,
        $Description = '',
        $Date = null,
        $CorrectionDate = null,
        $ReturnDate = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblTest $Entity */
        $Entity = $Manager->getEntityById('TblTest', $tblTest->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Entity->setDate($Date ? new \DateTime($Date) : null);
            $Entity->setCorrectionDate($CorrectionDate ? new \DateTime($CorrectionDate) : null);
            $Entity->setReturnDate($ReturnDate ? new \DateTime($ReturnDate) : null);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     * @param string $Grade
     * @param string $Comment
     * @return null|TblGradeStudentSubjectLink
     */
    public function createGradeToTest(
        TblTest $tblTest,
        TblPerson $tblPerson,
        $Grade = '',
        $Comment = ''
    ) {
        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblGradeStudentSubjectLink')
            ->findOneBy(array(
                TblGradeStudentSubjectLink::ATTR_TBL_TEST => $tblTest->getId(),
                TblGradeStudentSubjectLink::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblGradeStudentSubjectLink();
            $Entity->setTblTest($tblTest);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblDivision($tblTest->getServiceTblDivision());
            $Entity->setServiceTblSubject($tblTest->getServiceTblSubject());
            $Entity->setServiceTblPeriod($tblTest->getServiceTblPeriod());
            $Entity->setTblGradeType($tblTest->getTblGradeType());
            $Entity->setGrade($Grade);
            $Entity->setComment($Comment);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @return TblGradeStudentSubjectLink[]|bool
     */
    public function getGradeAllByTest(TblTest $tblTest)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblGradeStudentSubjectLink')->findBy(array(
            TblGradeStudentSubjectLink::ATTR_TBL_TEST => $tblTest->getId()
        ));

        return empty($EntityList) ? false : $EntityList;
    }

}