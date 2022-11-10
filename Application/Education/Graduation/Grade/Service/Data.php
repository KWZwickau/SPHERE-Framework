<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /**
     * @param $id
     *
     * @return false|TblGradeType
     */
    public function getGradeTypeById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType', $id);
    }

    /**
     * @param $id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeText', $id);
    }

    /**
     * @param $id
     *
     * @return false|TblTest
     */
    public function getTestById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTest', $id);
    }

    /**
     * @param $id
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreType', $id);
    }

    /**
     * @param $id
     *
     * @return false|TblTask
     */
    public function getTaskById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTask', $id);
    }
}