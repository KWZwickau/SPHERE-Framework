<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblHandyCap;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecial;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;

/**
 * Class Support
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Support extends Integration
{

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblSupportFocusType
     */
    public function createSupportFocusType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSupportFocusType')->findOneBy(array(
            TblSupportFocusType::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblSupportFocusType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblSpecialDisorderType
     */
    public function createSpecialDisorderType($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSpecialDisorderType')->findOneBy(array(
            TblSpecialDisorderType::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblSpecialDisorderType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return false|TblSupportType
     */
    public function createSupportType($Name = '', $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $this->getSupportTypeByName($Name);
        if (!$Entity) {
            $Entity = new TblSupportType();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson       $serviceTblPerson
     * @param TblSupportType  $tblSupportType
     * @param \DateTime       $Date
     * @param string          $PersonEditor
     * @param string          $Company
     * @param string          $PersonSupport
     * @param string          $SupportTime
     * @param string          $Remark
     *
     * @return TblSupport
     */
    public function createSupport(TblPerson $serviceTblPerson,
        TblSupportType $tblSupportType,
        $Date,
        $PersonEditor = '',
        $Company = '',
        $PersonSupport = '',
        $SupportTime = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSupport();
        $Entity->setDate($Date);
        $Entity->setServiceTblPerson($serviceTblPerson);
        $Entity->setPersonEditor($PersonEditor);
        $Entity->setTblSupportTyp($tblSupportType);
        $Entity->setCompany($Company);
        $Entity->setPersonSupport($PersonSupport);
        $Entity->setSupportTime($SupportTime);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblSupport          $tblSupport
     * @param TblSupportFocusType $tblSupportFocusType
     * @param bool                $IsPrimary
     *
     * @return bool|TblSupportFocus
     */
    public function createSupportFocus(TblSupport $tblSupport, TblSupportFocusType $tblSupportFocusType, $IsPrimary = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSupportFocus')->findOneBy(array(
            TblSupportFocus::ATTR_TBL_SUPPORT_FOCUS_TYPE => $tblSupportFocusType->getId(),
            TblSupportFocus::ATTR_TBL_SUPPORT => $tblSupport->getId(),
        ));

        if (null === $Entity) {
            $Entity = new TblSupportFocus();
            $Entity->setTblSupport($tblSupport);
            $Entity->setTblSupportFocusType($tblSupportFocusType);
            $Entity->setIsPrimary($IsPrimary);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $serviceTblPerson
     * @param \DateTime $Date
     * @param string    $PersonEditor
     * @param string    $Remark
     *
     * @return TblSpecial
     */
    public function createSpecial(TblPerson $serviceTblPerson,
        $Date,
        $PersonEditor = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSpecial();
        $Entity->setDate($Date);
        $Entity->setServiceTblPerson($serviceTblPerson);
        $Entity->setPersonEditor($PersonEditor);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblPerson $serviceTblPerson
     * @param \DateTime $Date
     * @param string    $PersonEditor
     * @param string    $Remark
     *
     * @return TblHandyCap
     */
    public function createHandyCap(TblPerson $serviceTblPerson,
        $Date,
        $PersonEditor = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblHandyCap();
        $Entity->setDate($Date);
        $Entity->setServiceTblPerson($serviceTblPerson);
        $Entity->setPersonEditor($PersonEditor);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblSpecial             $tblSpecial
     * @param TblSpecialDisorderType $tblSpecialDisorderType
     *
     * @return bool|TblSpecialDisorder
     */
    public function createSpecialDisorder(TblSpecial $tblSpecial, TblSpecialDisorderType $tblSpecialDisorderType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSpecialDisorder();
        $Entity->setTblSpecial($tblSpecial);
        $Entity->setTblSpecialDisorderType($tblSpecialDisorderType);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
        return $Entity;
    }

    /**
     * @param TblSupport     $tblSupport
     * @param TblSupportType $tblSupportType
     * @param \DateTime      $Date
     * @param string         $PersonEditor
     * @param string         $Company
     * @param string         $PersonSupport
     * @param string         $SupportTime
     * @param string         $Remark
     *
     * @return bool
     */
    public function updateSupport(TblSupport $tblSupport,
        TblSupportType $tblSupportType,
        $Date,
        $PersonEditor = '',
        $Company = '',
        $PersonSupport = '',
        $SupportTime = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSupport $Entity */
        $Entity = $Manager->getEntityById('TblSupport', $tblSupport->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date);
            $Entity->setPersonEditor($PersonEditor);
            $Entity->setTblSupportTyp($tblSupportType);
            $Entity->setCompany($Company);
            $Entity->setPersonSupport($PersonSupport);
            $Entity->setSupportTime($SupportTime);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSpecial $tblSpecial
     * @param \DateTime  $Date
     * @param string     $PersonEditor
     * @param string     $Remark
     *
     * @return bool
     */
    public function updateSpecial(TblSpecial $tblSpecial,
        $Date,
        $PersonEditor = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSpecial $Entity */
        $Entity = $Manager->getEntityById('TblSpecial', $tblSpecial->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date);
            $Entity->setPersonEditor($PersonEditor);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblHandyCap $tblHandyCap
     * @param \DateTime   $Date
     * @param string      $PersonEditor
     * @param string      $Remark
     *
     * @return bool
     */
    public function updateHandyCap(TblHandyCap $tblHandyCap,
        $Date,
        $PersonEditor = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblHandyCap $Entity */
        $Entity = $Manager->getEntityById('TblHandyCap', $tblHandyCap->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDate($Date);
            $Entity->setPersonEditor($PersonEditor);
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSupportFocusType
     */
    public function getSupportFocusTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSupportFocusType', $Id
        );
    }

    /**
     * @param $Name
     * @return bool|TblSupportFocusType
     */
    public function getSupportFocusTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSupportFocusType', array(TblSupportFocusType::ATTR_NAME => $Name)
        );
    }

    /**
     * @return bool|TblSupportFocusType[]
     */
    public function getSupportFocusTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSupportFocusType'
        );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblSpecialDisorderType
     */
    public function getSpecialDisorderTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSpecialDisorderType', $Id
        );
    }

    /**
     * @param $Name
     * @return bool|TblSpecialDisorderType
     */
    public function getSpecialDisorderTypeByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSpecialDisorderType', array(TblSpecialDisorderType::ATTR_NAME => $Name)
        );
    }

    /**
     * @return bool|TblSpecialDisorderType[]
     */
    public function getSpecialDisorderTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSpecialDisorderType');
    }

    /**
     * @return false|TblSupportType[]
     */
    public function getSupportTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupportType');
    }

    /**
     * @return string
     */
    public function countSupportAll()
    {

        return $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupport', array());
    }

    /**
     * @return string
     */
    public function countSpecialAll()
    {

        return $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSpecial', array());
    }

    /**
     * @param $Id
     *
     * @return false|TblSupportType
     */
    public function getSupportTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSupportType', $Id
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblSupport
     */
    public function getSupportById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSupport', $Id
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblSpecial
     */
    public function getSpecialById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblSpecial', $Id
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblSpecial
     */
    public function getHandyCapById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblHandyCap', $Id
        );
    }

    /**
     * @return false|TblSpecial[]
     */
    public function getHandyCapAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHandyCap');
    }



    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblSupport[]
     */
    public function getSupportByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupport',
        array(
            TblSupport::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSupportType $tblSupportType
     *
     * @return false|TblSupport[]
     */
    public function getSupportAllByPersonAndSupportType(TblPerson $tblPerson, TblSupportType $tblSupportType)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSupport',
            array(
                TblSupport::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblSupport::ATTR_TBL_SUPPORT_TYPE => $tblSupportType->getId()
            ),
            // Sortierung wichtig für Kamenz-Statistik
            array(
                TblSupport::ATTR_DATE => self::ORDER_DESC
            )
        );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblSpecial[]
     */
    public function getSpecialByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSpecial',
        array(
            TblSpecial::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblHandyCap[]
     */
    public function getHandyCapByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblHandyCap',
        array(
            TblHandyCap::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param string $Name
     *
     * @return false|TblSupportType
     */
    public function getSupportTypeByName($Name = '')
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupportType', array(
            TblSupportType::ATTR_NAME => $Name
        ));
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return false|TblSupportFocus[]
     */
    public function getSupportFocusBySupport(TblSupport $tblSupport)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupportFocus', array(
            TblSupportFocus::ATTR_TBL_SUPPORT => $tblSupport->getId()
        ));
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return false|TblSupportFocus
     */
    public function getSupportPrimaryFocusBySupport(TblSupport $tblSupport)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupportFocus',
            array(
                TblSupportFocus::ATTR_TBL_SUPPORT => $tblSupport->getId(),
                TblSupportFocus::ATTR_IS_PRIMARY => true
            )
        );
    }

    /**
     * @param TblSupport          $tblSupport
     * @param TblSupportFocusType $tblSupportFocusType
     *
     * @return bool|false|TblSupportFocus
     */
    public function getSupportFocusBySupportAndFocus(TblSupport $tblSupport, TblSupportFocusType $tblSupportFocusType)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupportFocus', array(
            TblSupportFocus::ATTR_TBL_SUPPORT => $tblSupport->getId(),
            TblSupportFocus::ATTR_TBL_SUPPORT_FOCUS_TYPE => $tblSupportFocusType->getId(),

        ));
    }

    /**
     * @param TblSpecial $tblSpecial
     *
     * @return false|TblSpecialDisorder[]
     */
    public function getSpecialDisorderBySpecial(TblSpecial $tblSpecial)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSpecialDisorder', array(
            TblSpecialDisorder::ATTR_TBL_SPECIAL => $tblSpecial->getId()
        ));
    }

    /**
     * @param TblSupport $tblSupport
     *
     * @return bool
     */
    public function deleteSupport(TblSupport $tblSupport)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSupport $Entity */
        $Entity = $Manager->getEntityById('TblSupport', $tblSupport->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSupportFocus $tblSupportFocus
     *
     * @return bool
     */
    public function deleteSupportFocus(TblSupportFocus $tblSupportFocus)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSupportFocus $Entity */
        $Entity = $Manager->getEntityById('TblSupportFocus', $tblSupportFocus->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSpecial $tblSpecial
     *
     * @return bool
     */
    public function deleteSpecial(TblSpecial $tblSpecial)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSpecial $Entity */
        $Entity = $Manager->getEntityById('TblSpecial', $tblSpecial->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSpecialDisorder $tblSpecialDisorder
     *
     * @return bool
     */
    public function deleteSpecialDisorder(TblSpecialDisorder $tblSpecialDisorder)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSpecialDisorder $Entity */
        $Entity = $Manager->getEntityById('TblSpecialDisorder', $tblSpecialDisorder->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblHandyCap $tblHandyCap
     *
     * @return bool
     */
    public function deleteHandyCap(TblHandyCap $tblHandyCap)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblHandyCap $Entity */
        $Entity = $Manager->getEntityById('TblHandyCap', $tblHandyCap->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
