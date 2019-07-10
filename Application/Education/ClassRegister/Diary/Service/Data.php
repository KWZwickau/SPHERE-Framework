<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service;

use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Education\ClassRegister\Diary\Service\Entity
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Subject
     * @param $Content
     * @param $Date
     * @param $Location
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivision|null $tblDivision
     * @param TblGroup|null $tblGroup
     *
     * @return TblDiary
     */
    public function createAbsence(
        $Subject,
        $Content,
        $Date,
        $Location,
        TblPerson $tblPerson,
        TblYear $tblYear,
        TblDivision $tblDivision = null,
        TblGroup $tblGroup = null
    ) {

        $Manager = $this->getEntityManager();

        $Entity = new TblDiary();
        $Entity->setSubject($Subject);
        $Entity->setContent($Content);
        $Entity->setDate($Date ? new \DateTime($Date) : null);
        $Entity->setLocation($Location);
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setServiceTblYear($tblYear);
        $Entity->setServiceTblDivision($tblDivision);
        $Entity->setServiceTblGroup($tblGroup);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDiary', $Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivision(TblDivision $tblDivision)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiary', array(
            TblDiary::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
        ));
    }
}