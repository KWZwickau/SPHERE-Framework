<?php
namespace SPHERE\Application\People\Meta\Masern\Service;

use DateTime;
use SPHERE\Application\People\Meta\Masern\Service\Entity\TblPersonMasern;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Masern\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblPersonMasern
     */
    public function getPersonMasernByPerson(TblPerson $tblPerson)
    {

        /** @var TblPersonMasern $Entity */
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblPersonMasern',
            array(
                TblPersonMasern::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblPerson                 $tblPerson
     * @param DateTime|null             $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return TblPersonMasern
     */
    public function createPersonMasern(
        TblPerson $tblPerson,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblPersonMasern();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setMasernDate($MasernDate);
        $Entity->setMasernDocumentType($MasernDocumentType);
        $Entity->setMasernCreatorType($MasernCreatorType);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblPersonMasern $tblPersonMasern
     * @param TblPerson $tblPerson
     * @param DateTime|null $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return bool
     */
    public function updatePersonMasern(
        TblPersonMasern $tblPersonMasern,
        $tblPerson,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblPersonMasern $Entity */
        $Entity = $Manager->getEntityById('TblPersonMasern', $tblPersonMasern->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setMasernDate($MasernDate);
            $Entity->setMasernDocumentType($MasernDocumentType);
            $Entity->setMasernCreatorType($MasernCreatorType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
