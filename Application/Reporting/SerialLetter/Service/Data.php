<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 27.04.2016
 * Time: 14:51
 */

namespace SPHERE\Application\Reporting\SerialLetter\Service;

use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Reporting\SerialLetter\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createType('Person', 'PERSON');
        $this->createType('Schüler', 'STUDENT');
        $this->createType('Interessent', 'PROSPECT');
        $this->createType('Familie', 'FAMILY');
    }

    /**
     * @param $Id
     *
     * @return bool|TblSerialLetter
     */
    public function getSerialLetterById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSerialLetter', $Id);
    }

    /**
     * @param $Identifier
     * @return bool|TblType
     */
    public function getTypeByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblType', array(
            TblType::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblType
     */
    public function createType(
        $Name,
        $Identifier
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblType')
            ->findOneBy(array(
                TblType::ATTR_NAME       => $Name,
                TblType::ATTR_IDENTIFIER => $Identifier
            ));

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }
}