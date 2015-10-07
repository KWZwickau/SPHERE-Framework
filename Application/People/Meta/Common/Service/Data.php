<?php
namespace SPHERE\Application\People\Meta\Common\Service;

use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommon;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Common\Service
 */
class Data extends Cacheable
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

    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblCommon
     */
    public function getCommonByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblCommon', array(
            TblCommon::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param string $Birthday
     * @param string $Birthplace
     * @param int    $Gender
     *
     * @return TblCommonBirthDates
     */
    public function createCommonBirthDates(
        $Birthday,
        $Birthplace,
        $Gender
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblCommonBirthDates();
        $Entity->setBirthday(( $Birthday ? new \DateTime($Birthday) : null ));
        $Entity->setBirthplace($Birthplace);
        $Entity->setGender($Gender);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param string $Nationality
     * @param string $Denomination
     * @param int    $IsAssistance
     * @param string $AssistanceActivity
     *
     * @return TblCommonInformation
     */
    public function createCommonInformation(
        $Nationality,
        $Denomination,
        $IsAssistance,
        $AssistanceActivity
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblCommonInformation();
        $Entity->setNationality($Nationality);
        $Entity->setDenomination($Denomination);
        $Entity->setIsAssistance($IsAssistance);
        $Entity->setAssistanceActivity($AssistanceActivity);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblPerson            $tblPerson
     * @param TblCommonBirthDates  $tblCommonBirthDates
     * @param TblCommonInformation $tblCommonInformation
     * @param string               $Remark
     *
     * @return TblCommon
     */
    public function createCommon(
        TblPerson $tblPerson,
        TblCommonBirthDates $tblCommonBirthDates,
        TblCommonInformation $tblCommonInformation,
        $Remark
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblCommon();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblCommonBirthDates($tblCommonBirthDates);
        $Entity->setTblCommonInformation($tblCommonInformation);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommon
     */
    public function getCommonById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblCommon', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonBirthDates
     */
    public function getCommonBirthDatesById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblCommonBirthDates',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblCommonInformation
     */
    public function getCommonInformationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblCommonInformation',
            $Id);
    }

    /**
     * @return bool|TblCommonInformation[]
     */
    public function getCommonInformationAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblCommonInformation');
    }

    /**
     * @return bool|TblCommonBirthDates[]
     */
    public function getCommonBirthDatesAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblCommonBirthDates');
    }

    /**
     * @param TblCommonBirthDates $tblCommonBirthDates
     * @param string              $Birthday
     * @param string              $Birthplace
     * @param int                 $Gender
     *
     * @return TblCommonBirthDates
     */
    public function updateCommonBirthDates(
        TblCommonBirthDates $tblCommonBirthDates,
        $Birthday,
        $Birthplace,
        $Gender
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblCommonBirthDates $Entity */
        $Entity = $Manager->getEntityById('TblCommonBirthDates', $tblCommonBirthDates->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setBirthday(( $Birthday ? new \DateTime($Birthday) : null ));
            $Entity->setBirthplace($Birthplace);
            $Entity->setGender($Gender);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCommonInformation $tblCommonInformation
     * @param string               $Nationality
     * @param string               $Denomination
     * @param int                  $IsAssistance
     * @param string               $AssistanceActivity
     *
     * @return TblCommonInformation
     */
    public function updateCommonInformation(
        TblCommonInformation $tblCommonInformation,
        $Nationality,
        $Denomination,
        $IsAssistance,
        $AssistanceActivity
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblCommonInformation $Entity */
        $Entity = $Manager->getEntityById('TblCommonInformation', $tblCommonInformation->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setNationality($Nationality);
            $Entity->setDenomination($Denomination);
            $Entity->setIsAssistance($IsAssistance);
            $Entity->setAssistanceActivity($AssistanceActivity);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblCommon $tblCommon
     * @param string    $Remark
     *
     * @return TblCommon
     */
    public function updateCommon(
        TblCommon $tblCommon,
        $Remark
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblCommon $Entity */
        $Entity = $Manager->getEntityById('TblCommon', $tblCommon->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
