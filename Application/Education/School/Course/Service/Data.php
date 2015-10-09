<?php
namespace SPHERE\Application\Education\School\Course\Service;

use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\School\Course\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createCourse('Hauptschule');
        $this->createCourse('Realschule');
        $this->createCourse('Gymnasium');
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return null|object|TblCourse
     */
    public function createCourse($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblCourse')
            ->findOneBy(array(TblCourse::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblCourse();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblCourse
     */
    public function getCourseById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCourse', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblCourse
     */
    public function getCourseByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblCourse')
            ->findOneBy(array(TblCourse::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCourse[]
     */
    public function getCourseAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCourse');
    }
}
