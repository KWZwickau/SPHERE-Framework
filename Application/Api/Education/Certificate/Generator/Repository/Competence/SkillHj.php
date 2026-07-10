<?php /** @noinspection PhpUnused */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\Competence;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class SkillHj extends SkillStyle
{
    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page|Page[]
     */
    public function buildPages(TblPerson $tblPerson = null) : Page|array
    {
        $certificateName = 'Kompetenz Halbjahresinformation';

        $personId = $tblPerson ? $tblPerson->getId() : 0;
        $tblSchoolType = $this->getTblSchoolType();

        $this->pageSliceList[++$this->pageCount] = [];

        $this->pageSliceList[$this->pageCount][] = $this->getHead($this->isSample());
        $this->pageSliceList[$this->pageCount][] = $this->getSchoolName($personId);
        $this->pageSliceList[$this->pageCount][] = $this->getCertificateHead(
                $certificateName . ($tblSchoolType ? ' der ' . $tblSchoolType->getName() : ''),
                '25px'
            );
        $this->pageSliceList[$this->pageCount][] = $this->getDivisionAndYear($personId, '25px', '1. Schulhalbjahr');
        $this->pageSliceList[$this->pageCount][] = $this->getStudentName($personId);

        // pdf höhe 1123px  => 26,2cm
        //	1,2 cm -> 51px (Rand oben)
        //	7,8 - 1,2 cm = 6,6-> 283
        $this->heightStartPixel = 283;

        // Fachnoten vom Stichtagsnotenauftrag, falls vorhanden
        $this->setSubjectLanes($personId);

        // Kompetenzen
        $this->setSkillContent($tblPerson);

        // für test viele Kompetenzen ansonsten auf false stellen
        if (false) {
            /** @noinspection PhpUnreachableStatementInspection */
            for ($i = 0; $i < 9; $i++) {
                $this->setSkillContent($tblPerson);
            }
        }

        $this->setRemark($personId);
//        $this->setTransfer($personId);
        $this->setDateLine($personId);
        $this->setSign($personId, false);
        $this->setInfo();

        return $this->preBuildPages($tblPerson, $certificateName);
    }
}