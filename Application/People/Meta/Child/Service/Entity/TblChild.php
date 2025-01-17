<?php

namespace SPHERE\Application\People\Meta\Child\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblChild")
 * @Cache(usage="READ_ONLY")
 */
class TblChild extends Element
{
    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="text")
     */
    protected $AuthorizedToCollect;

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
     * @return string
     */
    public function getAuthorizedToCollect()
    {
        return $this->AuthorizedToCollect;
    }

    /**
     * @param string $AuthorizedToCollect
     */
    public function setAuthorizedToCollect($AuthorizedToCollect)
    {
        $this->AuthorizedToCollect = $AuthorizedToCollect;
    }
}