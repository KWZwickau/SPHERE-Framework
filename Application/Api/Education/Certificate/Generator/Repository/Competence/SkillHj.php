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
        $personId = $tblPerson ? $tblPerson->getId() : 0;
        $tblSchoolType = $this->getTblSchoolType();

        // todo name des schülers und seiten anzahl auf jeder seite vgl. Abschlusszeugnisse
        return (new Page())
            ->addSlice($this->getHead($this->isSample()))
            ->addSlice($this->getSchoolName($personId))
            ->addSlice($this->getCertificateHead(
                'Kompetenz Halbjahresinformation' . ($tblSchoolType ? ' der ' . $tblSchoolType->getName() : ''),
                '25px'
            ))
            ->addSlice($this->getDivisionAndYear($personId, '25px', '1. Schulhalbjahr'))
            ->addSlice($this->getStudentName($personId))
            ->addSliceArray($this->getSkillContent($tblPerson))

//            ->addSlice($this->getDescriptionHead($personId, true))
//            ->addSlice($this->getDescriptionContent($personId, '35px', '5px'))
//            ->addSlice($this->getDateLine($personId, '10px'))
//            ->addSlice($this->getSignPart($personId, true))
//            ->addSlice($this->getParentSign('30px'))
//            ->addSlice($this->getInfo('2px',
//                'Für die Einschätzung der fachlichen Kompetenzen gilt folgende Skala:',
//                '1 - übertrifft die Anforderung - liegt deutlich über den Regelanforderungen und jahrgangsgemäßen Erwartungen',
//                '...'
//            ))
            ;
    }
}