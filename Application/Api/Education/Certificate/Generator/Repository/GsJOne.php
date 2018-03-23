<?php
namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

/**
 * Class GsJOne
 *
 * @package SPHERE\Application\Api\Education\Certificate\Certificate\Repository
 */
class GsJOne extends Certificate
{

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPages(TblPerson $tblPerson = null){

        $personId = $tblPerson ? $tblPerson->getId() : 0;

        $Header = $this->getHead($this->isSample(), true, 'auto', '50px');

        return (new Page())
                ->addSlice(
                    $Header
                )
                ->addSlice($this->getSchoolName($personId))
                ->addSlice($this->getCertificateHead('Jahreszeugnis der Grundschule'))
                ->addSlice($this->getDivisionAndYear($personId))
                ->addSlice($this->getStudentName($personId))
                ->addSlice($this->getDescriptionContent($personId, '620px', '20px'))
                ->addSlice($this->getDateLine($personId))
                ->addSlice($this->getSignPart($personId, true))
                ->addSlice($this->getParentSign()
        );
    }
}
