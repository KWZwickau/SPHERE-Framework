<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\Competence;

class SkillJ
{
    /**
     * @return array
     */
    public function selectValuesTransfer(): array
    {
        return array(
            1 => "wird versetzt",
            2 => "wird nicht versetzt"
        );
    }
}