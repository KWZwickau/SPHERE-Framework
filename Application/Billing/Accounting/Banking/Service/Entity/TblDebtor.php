<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtor")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtor extends Element
{


    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_PERSON_BILLING = 'tblPersonBilling';


    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblPersonBilling;

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
}
