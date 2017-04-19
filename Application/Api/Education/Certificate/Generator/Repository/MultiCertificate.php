<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 13.04.2017
 * Time: 09:59
 */

namespace SPHERE\Application\Api\Education\Certificate\Generator\Repository;

use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Education\Certificate\Generator\Repository\Document;
use SPHERE\Application\Education\Certificate\Generator\Repository\Frame;
use SPHERE\Application\Education\Certificate\Generator\Repository\Page;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;


class MultiCertificate extends Certificate
{

    /**
     * @param array $PageList
     * @return Frame
     * @internal param bool $IsSample
     */
    public function buildCertificate($PageList = array())
    {
        $buildDocument = new Document();
        foreach ($PageList as $page)
        {
            $buildDocument->addPage($page);
        }
        return (new Frame())->addDocument($buildDocument);
    }

    /**
     * @param TblPerson|null $tblPerson
     * @return Page
     * @internal param bool $IsSample
     *
     */
    public function buildPage(TblPerson $tblPerson = null)
    {
        return new Page();
    }
}