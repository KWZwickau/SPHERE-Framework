<?php
namespace SPHERE\Application\Reporting\CheckList\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group as CompanyGroup;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group as PersonGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Reporting\CheckList\CheckList;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblListObjectList")
 * @Cache(usage="READ_ONLY")
 */
class TblListObjectList extends Element
{

    const ATTR_TBL_LIST = 'tblList';
    const ATTR_SERVICE_TBL_OBJECT = 'serviceTblObject';
    const ATTR_TBL_OBJECT_TYPE = 'tblObjectType';

    /**
     * @Column(type="bigint")
     */
    protected $tblList;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblObject;

    /**
     * @Column(type="bigint")
     */
    protected $tblObjectType;

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

        $this->tblList = ( null === $tblList ? null : $tblList->getId() );
    }

    /**
     * @return bool|Element
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
            } elseif ($this->getTblObjectType()->getIdentifier() === 'PERSONGROUP') {
                return PersonGroup::useService()->getGroupById($this->serviceTblObject);
            } elseif ($this->getTblObjectType()->getIdentifier() === 'COMPANYGROUP') {
                return CompanyGroup::useService()->getGroupById($this->serviceTblObject);
            } elseif ($this->getTblObjectType()->getIdentifier() === 'DIVISIONGROUP') {
                return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblObject);
            }
        }

        return false;
    }

    /**
     * @param Element $serviceTblObject
     */
    public function setServiceTblObject($serviceTblObject)
    {

        $this->serviceTblObject = ( null === $serviceTblObject ? null : $serviceTblObject->getId() );
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

        $this->tblObjectType = ( null === $tblObjectType ? null : $tblObjectType->getId() );
    }
}
