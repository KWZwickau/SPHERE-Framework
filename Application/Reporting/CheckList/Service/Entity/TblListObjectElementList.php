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
            $tblList = $this->getTblList();
            if ($tblList) {
                $tblListType = $tblList->getTblListType();
                if ($tblListType) {
                    if ($tblListType->getIdentifier() === 'PERSON') {
                        return Person::useService()->getPersonById($this->serviceTblObject);
                    } else if ($tblListType->getIdentifier() === 'COMPANY') {
                        return Company::useService()->getCompanyById($this->serviceTblObject);
                    }
                }
            }

        }

        return false;
    }

    /**
     * @param TblCompany|TblPerson $serviceTblObject
     */
    public function setServiceTblObject($serviceTblObject)
    {
        $this->serviceTblObject = (null === $serviceTblObject ? null : $serviceTblObject->getId());
    }
}