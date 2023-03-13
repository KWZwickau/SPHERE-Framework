<?php

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareComplexExam;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareStudent;
use SPHERE\System\Database\Binding\AbstractData;

abstract class DataDiploma extends AbstractData
{
    /**
     * @param TblPrepareStudent $tblPrepareStudent
     * @param $identifier
     * @param $ranking
     *
     * @return false|TblPrepareComplexExam
     */
    public function getPrepareComplexExamBy(
        TblPrepareStudent $tblPrepareStudent,
        $identifier,
        $ranking
    ) {

        return $this->getCachedEntityBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareComplexExam',
            array(
                TblPrepareComplexExam::ATTR_TBL_PREPARE_STUDENT => $tblPrepareStudent->getId(),
                TblPrepareComplexExam::ATTR_IDENTIFIER => $identifier,
                TblPrepareComplexExam::ATTR_RANKING => $ranking
            )
        );
    }

    /**
     * @param TblPrepareStudent $tblPrepareStudent
     *
     * @return false|TblPrepareComplexExam[]
     */
    public function getPrepareComplexExamAllByPrepareStudent(TblPrepareStudent $tblPrepareStudent)
    {
        return $this->getCachedEntityListBy(
            __METHOD__,
            $this->getEntityManager(),
            'TblPrepareComplexExam',
            array(TblPrepareComplexExam::ATTR_TBL_PREPARE_STUDENT => $tblPrepareStudent->getId()),
            array(
                TblPrepareComplexExam::ATTR_IDENTIFIER => self::ORDER_DESC,
                TblPrepareComplexExam::ATTR_RANKING => self::ORDER_ASC
            )
        );
    }
}