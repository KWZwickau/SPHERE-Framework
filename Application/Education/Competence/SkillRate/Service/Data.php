<?php

namespace SPHERE\Application\Education\Competence\SkillRate\Service;

use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
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
}