<?php
namespace SPHERE\Application\Setting\Consumer\School\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblSchool;
use SPHERE\Application\Setting\Consumer\School\Service\Entity\TblType;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Extension\Extension;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Consumer\School\Service
 */
class Data extends Extension
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        $this->createType('Grundschule');
        $this->createType('Mittelschule');
        $this->createType('Oberschule');
        $this->createType('Gymnasium');
    }

    /**
     * @param string $Name
     *
     * @return TblType
     */
    public function createType($Name)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblType')->findOneBy(array(
            TblType::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblType', $Id);

        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSchool
     */
    public function getSchoolById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblSchool', $Id);

        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblSchool[]
     */
    public function getSchoolAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblSchool')->findAll();

        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblType')->findAll();

        return ( empty ( $EntityList ) ? false : $EntityList );
    }


    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return TblSchool|bool
     */
    public function addSchool(TblCompany $tblCompany, TblType $tblType)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblSchool')
            ->findOneBy(array(
                TblSchool::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblSchool::ATT_TBL_TYPE        => $tblType->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblSchool();
            $Entity->setServiceTblCompany($tblCompany);
            $Entity->setTblType($tblType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
            return $Entity;
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

        $Manager = $this->Connection->getEntityManager();
        /** @var TblSchool $Entity */
        $Entity = $Manager->getEntityById('TblSchool', $tblSchool->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
