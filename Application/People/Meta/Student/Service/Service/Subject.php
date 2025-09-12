<?php
namespace SPHERE\Application\People\Meta\Student\Service\Service;

use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;

/**
 * Class Transfer
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Service
 */
abstract class Subject extends Transfer
{

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentSubjectType $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblSubject $tblSubject
     * @param int|null $LevelFrom
     * @param int|null $LevelTill
     *
     * @return TblStudentSubject
     */
    public function addStudentSubject(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblSubject $tblSubject,
        ?int $LevelFrom = null,
        ?int $LevelTill = null
    ): TblStudentSubject {
        return (new Data($this->getBinding()))->addStudentSubject($tblStudent, $tblStudentSubjectType,
            $tblStudentSubjectRanking, $tblSubject, $LevelFrom, $LevelTill);
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool|TblStudentSubject[]
     */
    public function getStudentSubjectAllByStudent(TblStudent $tblStudent)
    {

        return (new Data($this->getBinding()))->getStudentSubjectAllByStudent($tblStudent);
    }

    public function getStudentSubjectByStudentAndSubjectAndSubjectRanking(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking
    ) {
        return ( new Data($this->getBinding()) )->getStudentSubjectByStudentAndSubjectAndSubjectRanking($tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubject
     */
    public function getStudentSubjectById($Id)
    {

        return (new Data($this->getBinding()))->getStudentSubjectById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubjectType
     */
    public function getStudentSubjectTypeById($Id)
    {

        return (new Data($this->getBinding()))->getStudentSubjectTypeById($Id);
    }

    /**
     * @return bool|TblStudentSubjectType[]
     */
    public function getStudentSubjectTypeAll()
    {

        return (new Data($this->getBinding()))->getStudentSubjectTypeAll();
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentSubjectType
     */
    public function getStudentSubjectTypeByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getStudentSubjectTypeByIdentifier($Identifier);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentSubjectRanking
     */
    public function getStudentSubjectRankingById($Id)
    {

        return (new Data($this->getBinding()))->getStudentSubjectRankingById($Id);
    }

    /**
     * @return bool|TblStudentSubjectRanking[]
     */
    public function getStudentSubjectRankingAll()
    {

        return (new Data($this->getBinding()))->getStudentSubjectRankingAll();
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblStudentSubjectRanking
     */
    public function getStudentSubjectRankingByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getStudentSubjectRankingByIdentifier($Identifier);
    }

    /**
     * @param TblStudent                   $tblStudent
     * @param string|TblStudentSubjectType $tblStudentSubjectType
     *
     * @return bool|TblStudentSubject[]
     */
    public function getStudentSubjectAllByStudentAndSubjectType(TblStudent $tblStudent, $tblStudentSubjectType)
    {

        if(null !== $tblStudentSubjectType && !($tblStudentSubjectType instanceof TblStudentSubjectType)){
            $tblStudentSubjectType = $this->getStudentSubjectTypeByIdentifier($tblStudentSubjectType);
        }
        return (new Data($this->getBinding()))->getStudentSubjectAllByStudentAndSubjectType($tblStudent,
            $tblStudentSubjectType);
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentSubjectType $tblStudentSubjectType
     *
     * @return false|TblStudentSubject
     */
    public function getFirstStudentSubjectByStudentAndSubjectType(TblStudent $tblStudent, TblStudentSubjectType $tblStudentSubjectType): bool|TblStudentSubject {
        return (new Data($this->getBinding()))->getFirstStudentSubjectByStudentAndSubjectType($tblStudent, $tblStudentSubjectType);
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentSubjectType $tblStudentSubjectType
     *
     * @return bool|TblSubject
     */
    public function getFirstSubjectByStudentAndSubjectType(TblStudent $tblStudent, TblStudentSubjectType $tblStudentSubjectType): TblSubject|bool
    {
        if (($tblStudentSubject = $this->getFirstStudentSubjectByStudentAndSubjectType($tblStudent, $tblStudentSubjectType))) {
            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }
}
