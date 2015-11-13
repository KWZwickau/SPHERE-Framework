<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentBilling")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentBilling extends Element
{

    // TODO: Connect to Billing
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSiblingRank;

    /**
     * @return bool|TblSiblingRank
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblSiblingRank) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblSiblingRank);
        }
    }

    /**
     * @param TblSiblingRank|null $tblSiblingRank
     */
    public function setServiceTblType(TblSiblingRank $tblSiblingRank = null)
    {

        $this->serviceTblSiblingRank = ( null === $tblSiblingRank ? null : $tblSiblingRank->getId() );
    }
}
