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
}