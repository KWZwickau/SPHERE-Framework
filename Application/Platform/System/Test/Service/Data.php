<?php
namespace SPHERE\Application\Platform\System\Test\Service;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Test\Service\Entity\TblPicture;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\System\Test\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblPicture
     */
    public function getPictureByPerson(TblPerson $tblPerson)
    {

        /** @var TblPicture $Entity */
        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblPicture',
            array(TblPicture::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $File
     *
     * @return object|TblPicture|null
     */
    public function createPicture(TblPerson $tblPerson, $File)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPicture')->findOneBy(array(
            TblPicture::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
        ));

        if(null === $Entity){
            // create
            $Entity = new TblPicture();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setFile($File);

            $Manager->saveEntity($Entity);
//            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
//                $Entity);
        } else {
            // update
            $Entity->setFile($File);
            $Manager->saveEntity($Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $File
     *
     * @return object|TblPicture|null
     */
    public function updatePicture(TblPicture $tblPicture, $File)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityClone = clone $tblPicture;
        $tblPicture->setFile($File);

        $Manager->saveEntity($tblPicture);
//        Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $EntityClone, $tblPicture);
    }

    /**
     * @param TblPicture $tblPicture
     */
    public function destroyPicture(TblPicture $tblPicture)
    {

        $Manager = $this->getConnection()->getEntityManager();
//        Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $tblPicture);

        $Manager->killEntity($tblPicture);
    }


}
