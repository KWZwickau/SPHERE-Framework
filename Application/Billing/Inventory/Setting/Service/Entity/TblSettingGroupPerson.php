<?php
namespace SPHERE\Application\Billing\Inventory\Setting\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSettingGroupPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblSettingGroupPerson extends Element
{

    const ATTR_SERVICE_TBL_GROUP_PERSON = 'serviceTblGroupPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroupPerson;

    /**
     * @return false|TblGroup
     */
    public function getServiceTblGroupPerson()
    {
        if(null === $this->serviceTblGroupPerson){
            return false;
        } else {
            return Group::useService()->getGroupById($this->serviceTblGroupPerson);
        }
    }

    /**
     * @param TblGroup $tblGroup
     */
    public function setServiceTblGroupPerson($tblGroup)
    {
        $this->serviceTblGroupPerson = $tblGroup;
    }


}