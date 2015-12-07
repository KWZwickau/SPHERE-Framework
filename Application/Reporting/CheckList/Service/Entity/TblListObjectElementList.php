<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:41
 */

namespace SPHERE\Application\Reporting\CheckList\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\CheckList\CheckList;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblListObjectElementList")
 * @Cache(usage="READ_ONLY")
 */
class TblListObjectElementList extends Element
{
    const ATTR_TBL_LIST = 'tblList';
    const ATTR_TBL_LIST_ELEMENT_LIST = 'tblListElementList';
    const ATTR_SERVICE_TBL_OBJECT = 'serviceTblObject';
    const ATTR_TBL_OBJECT_TYPE = 'tblObjectType';

    /**
     * @Column(type="bigint")
     */
    protected $tblList;

    /**
     * @Column(type="bigint")
     */
    protected $tblListElementList;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblObject;

    /**
     * @Column(type="bigint")
     */
    protected $tblObjectType;

    /**
     * @Column(type="string")
     */
    protected $Value;

    /**
     * @return bool|TblList
     */
    public function getTblList()
    {
        if (null === $this->tblList) {
            return false;
        } else {
            return CheckList::useService()->getListById($this->tblList);
        }
    }

    /**
     * @param TblList|null $tblList
     */
    public function setTblList($tblList)
    {
        $this->tblList = (null === $tblList ? null : $tblList->getId());
    }

    /**
     * @return bool|TblListElementList
     */
    public function getTblListElementList()
    {
        if (null === $this->tblListElementList) {
            return false;
        } else {
            return CheckList::useService()->getListElementListById($this->tblListElementList);
        }
    }

    /**
     * @param TblListElementList|null $tblListElementList
     */
    public function setTblListElementList($tblListElementList)
    {
        $this->tblListElementList = (null === $tblListElementList ? null : $tblListElementList->getId());
    }

    /**
     * @return bool|TblCompany|TblPerson
     */
    public function getServiceTblObject()
    {
        if (null === $this->serviceTblObject) {
            return false;
        } else {
            if ($this->getTblObjectType()->getIdentifier() === 'PERSON') {
                return Person::useService()->getPersonById($this->serviceTblObject);
            } elseif ($this->getTblObjectType()->getIdentifier() === 'COMPANY') {
                return Company::useService()->getCompanyById($this->serviceTblObject);
            }
        }

        return false;
    }

    /**
     * @param Element $serviceTblObject
     */
    public function setServiceTblObject($serviceTblObject)
    {
        $this->serviceTblObject = (null === $serviceTblObject ? null : $serviceTblObject->getId());
    }

    /**
     * @return bool|TblObjectType
     */
    public function getTblObjectType()
    {
        if (null === $this->tblObjectType) {
            return false;
        } else {
            return CheckList::useService()->getObjectTypeById($this->tblObjectType);
        }
    }

    /**
     * @param TblObjectType|null $tblObjectType
     */
    public function setTblObjectType($tblObjectType)
    {
        $this->tblObjectType = (null === $tblObjectType ? null : $tblObjectType->getId());
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }
}