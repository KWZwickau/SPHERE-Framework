<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecial;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSpecialDisorder;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupport;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocus;
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
     * @param TblPerson|null  $serviceTblPersonEditor
     * @param TblCompany|null $serviceTblCompany
     * @param string          $PersonSupport
     * @param string          $SupportTime
     * @param string          $Remark
     *
     * @return TblSupport
     */
    public function createSupport(TblPerson $serviceTblPerson,
        TblSupportType $tblSupportType,
        $Date,
        TblPerson $serviceTblPersonEditor = null,
        TblCompany $serviceTblCompany = null,
        $PersonSupport = '',
        $SupportTime = '',
        $Remark = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSupport();
        $Entity->setDate($Date);
        $Entity->setServiceTblPerson($serviceTblPerson);
        $Entity->setServiceTblPersonEditor($serviceTblPersonEditor);
        $Entity->setTblSupportTyp($tblSupportType);
        $Entity->setServiceTblCompany($serviceTblCompany);
        $Entity->setPersonSupport($PersonSupport);
        $Entity->setSupportTime($SupportTime);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblSupport          $tblSupport
     * @param TblStudentFocusType $tblStudentFocusType
     * @param bool                $IsPrimary
     *
     * @return bool|TblSupportFocus
     */
    public function createSupportFocus(TblSupport $tblSupport, TblStudentFocusType $tblStudentFocusType, $IsPrimary = false)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSupportFocus();
        $Entity->setTblSupport($tblSupport);
        $Entity->setTblStudentFocusType($tblStudentFocusType);
        $Entity->setIsPrimary($IsPrimary);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
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
     * @param TblSpecial             $tblSpecial
     * @param TblStudentDisorderType $tblStudentDisorderType
     *
     * @return bool|TblSpecialDisorder
     */
    public function createSpecialDisorder(TblSpecial $tblSpecial, TblStudentDisorderType $tblStudentDisorderType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblSpecialDisorder();
        $Entity->setTblSpecial($tblSpecial);
        $Entity->setTblStudentDisorderType($tblStudentDisorderType);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity, true);
        return $Entity;
    }

    /**
     * @return false|TblSupportType[]
     */
    public function getSupportTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSupportType');
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
}
