<?php
namespace SPHERE\Application\People\Meta\Agreement\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPersonAgreement")
 * @Cache(usage="READ_ONLY")
 */
class TblPersonAgreement extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_PERSON_AGREEMENT_TYPE = 'tblPersonAgreementType';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblPersonAgreementType;

    /**
     * @return bool|TblPerson
     */
    public function getserviceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPersonAgreementType
     */
    public function getTblPersonAgreementType()
    {

        if (null === $this->tblPersonAgreementType) {
            return false;
        } else {
            return Agreement::useService()->getPersonAgreementTypeById($this->tblPersonAgreementType);
        }
    }

    /**
     * @param TblPersonAgreementType|null $tblPersonAgreementType
     */
    public function setTblPersonAgreementType(TblPersonAgreementType $tblPersonAgreementType = null)
    {

        $this->tblPersonAgreementType = ( null === $tblPersonAgreementType ? null : $tblPersonAgreementType->getId() );
    }
}
