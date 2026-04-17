<?php

namespace SPHERE\Application\Education\Competence\SkillRate\Service;

use DateTime;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeItem;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkillRate;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data extends AbstractData
{
    /**
     * @return void
     */
    public function setupDatabaseContent(): void
    {

    }

    /**
     * @param $id
     *
     * @return TblStudentSkill|false
     */
    public function getStudentSkillById($id): false|TblStudentSkill
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblStudentSkill', $id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSkill $tblSkill
     *
     * @return false|TblStudentSkill
     */
    public function getStudentSkillBy(TblPerson $tblPerson, TblYear $tblYear, TblSkill $tblSkill): false|TblStudentSkill
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentSkill', [
            TblStudentSkill::SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentSkill::SERVICE_TBL_YEAR => $tblYear->getId(),
            TblStudentSkill::SERVICE_TBL_SKILL => $tblSkill->getId(),
        ]);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param TblPerson|null $tblPersonTeacher
     * @param TblSkill|null $tblSkill
     * @param string|null $skillArea
     * @param string|null $skillLevel
     * @param string $skill
     *
     * @return TblStudentSkill
     */
    public function createStudentSkill(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, ?TblSkill $tblSkill,
        ?TblPerson $tblPersonTeacher, ?string $skillArea, ?string $skillLevel, string $skill): TblStudentSkill
    {
        $manager = $this->getEntityManager();

        $entity = new TblStudentSkill();
        $entity->setServiceTblPerson($tblPerson);
        $entity->setServiceTblYear($tblYear);
        $entity->setServiceTblSubject($tblSubject);
        $entity->setServiceTblPersonTeacher($tblPersonTeacher);
        $entity->setServiceTblSkill($tblSkill);
        $entity->setSkillArea($skillArea);
        $entity->setSkillLevel($skillLevel);
        $entity->setSkill($skill);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param TblStudentSkill $tblStudentSkill
     * @param TblPerson|null $tblPersonTeacher
     * @param DateTime $dateTime
     * @param string|null $comment
     * @param string $rate
     * @param TblScoreTypeItem|null $tblScoreTypeItem
     *
     * @return TblStudentSkillRate
     */
    public function createStudentSkillRate(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, TblStudentSkill $tblStudentSkill,
        ?TblPerson $tblPersonTeacher, DateTime $dateTime, ?string $comment, string $rate, ?TblScoreTypeItem $tblScoreTypeItem): TblStudentSkillRate
    {
        $manager = $this->getEntityManager();

        $entity = new TblStudentSkillRate();
        $entity->setServiceTblPerson($tblPerson);
        $entity->setServiceTblYear($tblYear);
        $entity->setServiceTblSubject($tblSubject);
        $entity->setServiceTblPersonTeacher($tblPersonTeacher);
        $entity->setTblStudentSkill($tblStudentSkill);
        $entity->setDate($dateTime);
        $entity->setComment($comment);
        $entity->setRate($rate);
        $entity->setServiceTblScoreTypeItem($tblScoreTypeItem);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }
}