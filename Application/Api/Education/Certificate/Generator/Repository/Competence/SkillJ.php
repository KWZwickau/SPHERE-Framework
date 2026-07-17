<?php /** @noinspection PhpUnused */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\Competence;

use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

class SkillJ extends SkillStyle
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

    /**
     * @param TblPerson|null $tblPerson
     *
     * @return Page|Page[]
     */
    public function buildPages(TblPerson $tblPerson = null) : Page|array
    {
        $certificateName = 'Kompetenz Jahreszeugnis';

        $personId = $tblPerson ? $tblPerson->getId() : 0;
        $tblSchoolType = $this->getTblSchoolType();

        $this->pageSliceList[++$this->pageCount] = [];

        $this->pageSliceList[$this->pageCount][] = $this->getHead($this->isSample());
        $this->pageSliceList[$this->pageCount][] = $this->getSchoolName($personId);
        $this->pageSliceList[$this->pageCount][] = $this->getCertificateHead(
            $certificateName . ($tblSchoolType ? ' der ' . $tblSchoolType->getName() : ''),
            '25px'
        );
        $this->pageSliceList[$this->pageCount][] = $this->getDivisionAndYear($personId, '25px');
        $this->pageSliceList[$this->pageCount][] = $this->getStudentName($personId);
        $this->heightStartPixel = 283;
        // Fachnoten vom Stichtagsnotenauftrag, falls vorhanden
        $this->setSubjectLanes($personId);
        // Kompetenzen
        $this->setSkillContent($tblPerson);

        // für test viele Kompetenzen ansonsten auf false stellen
        if (false) {
            /** @noinspection PhpUnreachableStatementInspection */
            for ($i = 0; $i < 9; $i++) {
                $this->setSkillContent(Person::useService()->getPersonById(972));
            }
        }

        $this->setRemark($personId);
        $this->setTransfer($personId);
        $this->setDateLine($personId);
        $this->setSign($personId, true);
        $this->setInfo();

        return $this->preBuildPages($tblPerson, $certificateName);
    }
}