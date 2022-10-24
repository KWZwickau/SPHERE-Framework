<?php
namespace SPHERE\Application\Setting\Consumer\School\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Consumer\School\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSchool
     */
    public function getSchoolById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchool', $Id);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return false|TblSchool[]
     */
    public function getSchoolByCompany(TblCompany $tblCompany)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchool',
            array(TblSchool::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId()));
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return false|TblSchool
     */
    public function getSchoolByCompanyAndType(TblCompany $tblCompany, TblType $tblType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchool',
            array(
                TblSchool::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblSchool::ATTR_SERVICE_TBL_TYPE    => $tblType->getId()
            ));
    }

    /**
     * @param TblType $tblType
     *
     * @return false|TblSchool[]
     */
    public function getSchoolByType(TblType $tblType){

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchool',
            array(
                TblSchool::ATTR_SERVICE_TBL_TYPE => $tblType->getId()
            ));
    }

    /**
     * @return bool|TblSchool[]
     */
    public function getSchoolAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchool', array(Element::ENTITY_CREATE => self::ORDER_ASC));
    }


    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     * @param string     $CompanyNumber
     *
     * @return bool|TblSchool
     */
    public function addSchool(TblCompany $tblCompany, TblType $tblType, $CompanyNumber = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSchool')
            ->findOneBy(array(
                TblSchool::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblSchool::ATTR_SERVICE_TBL_TYPE    => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblSchool();
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setTblType($tblType);
            $Entity->setCompanyNumber($CompanyNumber);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblSchool $tblSchool
     * @param string    $CompanyNumber
     *
     * @return bool
     */
    public function updateSchool(TblSchool $tblSchool, $CompanyNumber = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSchool $Entity */
        $Entity = $Manager->getEntityById('TblSchool', $tblSchool->getId());
        if ($Entity) {
            $Protocol = clone $Entity;
            $Entity->setCompanyNumber($CompanyNumber);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblSchool $tblSchool
     *
     * @return bool
     */
    public function removeSchool(TblSchool $tblSchool)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSchool $Entity */
        $Entity = $Manager->getEntityById('TblSchool', $tblSchool->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
