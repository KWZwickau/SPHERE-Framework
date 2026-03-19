<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterForgottenStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblForgottenStudent extends Element
{
    const ATTR_TBL_FORGOTTEN = 'tblClassRegisterForgotten';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterForgotten;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblForgotten
     */
    public function getTblForgotten(): TblForgotten
    {

        if (null === $this->tblClassRegisterForgotten) {
            return false;
        } else {
            return Digital::useService()->getForgottenById($this->tblClassRegisterForgotten);
        }
    }

    /**
     * @param TblForgotten|null $tblForgotten
     */
    public function setTblForgotten(TblForgotten $tblForgotten = null): void
    {

        $this->tblClassRegisterForgotten = $tblForgotten?->getId();
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson(): bool|TblPerson
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
    public function setServiceTblPerson(TblPerson $tblPerson = null): void
    {
        $this->serviceTblPerson = $tblPerson?->getId();
    }
}