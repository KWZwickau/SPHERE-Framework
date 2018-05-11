<?php
namespace SPHERE\Application\Contact\Web\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblToPerson extends Element
{

    const ATT_TBL_TYPE = 'tblType';
    const ATT_TBL_WEB = 'tblWeb';
    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblType;
    /**
     * @Column(type="bigint")
     */
    protected $tblWeb;

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
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return bool|TblType
     */
    public function getTblType()
    {

        if (null === $this->tblType) {
            return false;
        } else {
            return Web::useService()->getTypeById($this->tblType);
        }
    }

    /**
     * @param null|TblType $tblType
     */
    public function setTblType(TblType $tblType = null)
    {

        $this->tblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblWeb
     */
    public function getTblWeb()
    {

        if (null === $this->tblWeb) {
            return false;
        } else {
            return Web::useService()->getWebById($this->tblWeb);
        }
    }

    /**
     * @param null|TblWeb $tblWeb
     */
    public function setTblWeb(TblWeb $tblWeb = null)
    {

        $this->tblWeb = ( null === $tblWeb ? null : $tblWeb->getId() );
    }
}
