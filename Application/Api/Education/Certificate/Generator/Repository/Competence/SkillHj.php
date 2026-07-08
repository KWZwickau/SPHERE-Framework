<?php /** @noinspection PhpUnused */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository\Competence;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\Education\Certificate\Generator\Repository\Slice;
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

        $this->pageSliceList[++$this->pageCount] = [];

        $this->pageSliceList[$this->pageCount][] = $this->getHead($this->isSample());
        $this->pageSliceList[$this->pageCount][] = $this->getSchoolName($personId);
        $this->pageSliceList[$this->pageCount][] = $this->getCertificateHead(
                'Kompetenz Halbjahresinformation' . ($tblSchoolType ? ' der ' . $tblSchoolType->getName() : ''),
                '25px'
            );
        $this->pageSliceList[$this->pageCount][] = $this->getDivisionAndYear($personId, '25px', '1. Schulhalbjahr');
        $this->pageSliceList[$this->pageCount][] = $this->getStudentName($personId);

        // pdf höhe 1123px  => 26,2cm
        //	1,2 cm -> 51px (Rand oben)
        //	7,8 - 1,2 cm = 6,6-> 283
        $this->heightStartPixel = 283;

        $this->setSkillContent($tblPerson);

        // für test ansonsten auf false stellen
        if (true) {
            for ($i = 0; $i < 9; $i++) {
                $this->setSkillContent($tblPerson);
            }
        }


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
//            ;

        $pageList = [];
        foreach ($this->pageSliceList as $i => $pageSlices) {
            $page = new Page();
            // Kopfzeile
            if ($i > 1) {
                $page->addSlice((new Slice)
                    ->addElement((new Element())
                        ->setContent(
                            $tblPerson->getFirstSecondName() . ' ' . $tblPerson->getLastName()
                            . ', geboren am {{ Content.P' . $tblPerson->getId() . '.Person.Common.BirthDates.Birthday }} - ' . $i . '. Seite von '
                            . $this->pageCount . ' Seiten'
                        )
                    )
                    ->styleAlignCenter()
                    ->stylePaddingTop('10px')
                    ->styleBorderBottom('0.5px')
                    ->styleMarginBottom('20px')
                );
            }

            $page->addSliceArray($pageSlices);
            $pageList[] = $page;
        }

        return $pageList;
    }
}