<?php
namespace SPHERE\Application\People\Group\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblMember")
 * @Cache(usage="READ_ONLY")
 */
class TblMember extends Element
{

    const ATTR_TBL_GROUP = 'tblGroup';
    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(nullable=true)
     * @ManyToOne(targetEntity="TblGroup",fetch="EAGER",cascade={"persist"})
     * @JoinColumn(name="tblGroup",referencedColumnName="Id")
     */
    protected $tblGroup;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblGroup
     */
    public function getTblGroup()
    {

        if (null === $this->tblGroup) {
            return false;
        } else {
            if (is_object($this->tblGroup)) {
                return $this->tblGroup;
            } else {
                return Group::useService()->getGroupById($this->tblGroup);
            }
        }
    }

    /**
     * @param null|TblGroup $tblGroup
     */
    public function setTblGroup(TblGroup $tblGroup = null)
    {

        $this->tblGroup = (null === $tblGroup ? null : $tblGroup);
    }
}
