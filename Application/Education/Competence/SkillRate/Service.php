<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use SPHERE\Application\Education\Competence\SkillRate\Service\Data;
use SPHERE\Application\Education\Competence\SkillRate\Service\Entity\TblStudentSkill;
use SPHERE\Application\Education\Competence\SkillRate\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param $doSimulation
     * @param $withData
     * @param $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol = '';
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $id
     *
     * @return TblStudentSkill|false
     */
    public function getStudentSkillById($id): false|TblStudentSkill
    {
        return (new Data($this->getBinding()))->$this->getStudentSkillById($id);
    }
}