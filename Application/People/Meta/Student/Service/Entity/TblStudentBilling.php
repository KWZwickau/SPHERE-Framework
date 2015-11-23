<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentBilling")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentBilling extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSiblingRank;

    /**
     * @return bool|TblSiblingRank
     */
    public function getServiceTblSiblingRank()
    {

        if (null === $this->serviceTblSiblingRank) {
            return false;
        } else {
            return Relationship::useService()->getSiblingRankById($this->serviceTblSiblingRank);
        }
    }

    /**
     * @param TblSiblingRank|null $tblSiblingRank
     */
    public function setServiceTblSiblingRank(TblSiblingRank $tblSiblingRank = null)
    {

        $this->serviceTblSiblingRank = ( null === $tblSiblingRank ? null : $tblSiblingRank->getId() );
    }
}
