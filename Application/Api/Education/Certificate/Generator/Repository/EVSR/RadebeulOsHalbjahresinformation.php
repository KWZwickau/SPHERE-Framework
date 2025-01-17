<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.11.2018
 * Time: 10:53
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class RadebeulOsHalbjahresinformation
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator\Repository\EVSR
 */
class RadebeulOsHalbjahresinformation extends Certificate
{
    const TEXT_COLOR_BLUE = 'rgb(25,59,100)';
    const FONT_FAMILY = 'MetaPro';

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page
     */
    public function buildPages(TblPerson $tblPerson = null)
    {
        $gradeFieldWidth = 18;

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $gradeLanesSlice = $this->getGradeLanesForRadebeul(
            $personId,
            self::TEXT_COLOR_BLUE,
            '10pt',
            'rgb(224,226,231)',
            false,
            '20px',
            $gradeFieldWidth
        );

        $subjectLanesSlice = $this->getSubjectLanesForRadebeul(
            $personId,
            self::TEXT_COLOR_BLUE,
            '10pt',
            'rgb(224,226,231)',
            false,
            '8px',
            $gradeFieldWidth,
            self::FONT_FAMILY,
            '265px',
            true
        );

        return (new Page())
//        (new Page())
            ->addSlice(RadebeulOsJahreszeugnis::getHeader('Halbjahresinformation'))
            ->addSliceArray((new RadebeulOsJahreszeugnis(
                $this->getTblStudentEducation() ?: null,
                $this->getTblPrepareCertificate() ?: null
            ))->getBody(
                $personId,
                false,
                $gradeLanesSlice,
                $subjectLanesSlice
        ))
            ;
//        exit;
    }
}